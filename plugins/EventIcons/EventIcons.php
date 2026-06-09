<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Plugin;

class EventIcons extends Plugin
{
    public function registerEvents()
    {
        return [
            'Template.jsGlobalVariables' => 'addJsGlobalVariables',
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function addJsGlobalVariables(&$out)
    {
        $settings = new Settings\SystemSettings();
        $mapping = $settings->eventIconMapping->getValue();

        $lookup = [];
        if (!empty($mapping)) {
            foreach ($mapping as $entry) {
                if (!empty($entry['key']) && !empty($entry['icon'])) {
                    $lookup[$entry['key']] = $entry['icon'];
                }
            }
        }

        $out .= 'window.matomoEventIcons = ' . json_encode($lookup) . ";\n";
        $out .= 'window.matomoEventIconsBaseUrl = "plugins/EventIcons/icons/material/";' . "\n";
    }

    public function getJavaScriptFiles(&$files)
    {
        $files[] = 'plugins/EventIcons/javascripts/eventicons.js';
    }

    public function getStylesheetFiles(&$files)
    {
        $files[] = 'plugins/EventIcons/stylesheets/eventicons.less';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'EventIcons_IconReplaced';
        $translationKeys[] = 'EventIcons_Event';
    }
}
