<?php

namespace Piwik\Plugins\EventIcons\Settings;

use Piwik\Settings\FieldConfig;
use Piwik\Settings\Plugin\SystemSettings as PluginSystemSettings;

class SystemSettings extends PluginSystemSettings
{
    /** @var \Piwik\Settings\Setting */
    public $eventIconMapping;

    protected function init()
    {
        $this->eventIconMapping = $this->makeSetting('eventIconMapping', [], FieldConfig::TYPE_ARRAY, function (FieldConfig $field) {
            $field->title = 'Event Icon Mapping';
            $field->description = 'Mapping of event category/action pairs to icon names.';
            $field->uiControl = FieldConfig::UI_CONTROL_TEXTAREA;
            $field->readableByCurrentUser = true;
        });
    }
}
