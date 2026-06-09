<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;


class Controller extends ControllerAdmin
{
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $settings = new Settings\SystemSettings();
        $mapping = $settings->eventIconMapping->getValue() ?: [];

        $sites = SitesManagerAPI::getInstance()->getSitesWithAdminAccess();

        return $this->renderTemplate('settings', [
            'title' => Piwik::translate('EventIcons_AdminMenuTitle'),
            'mapping' => json_encode($mapping),
            'iconList' => json_encode($this->getAvailableIcons()),
            'sites' => $sites,
        ]);
    }

    public function save()
    {
        Piwik::checkUserHasSuperUserAccess();

        $mapping = Common::getRequestVar('mapping', [], 'json');

        if (!is_array($mapping)) {
            $mapping = [];
        }

        $settings = new Settings\SystemSettings();
        $settings->eventIconMapping->setValue($mapping);
        $settings->save();

        return $this->jsonResponse(['success' => true]);
    }

    public function detectEvents()
    {
        Piwik::checkUserHasSuperUserAccess();

        $idSite = Common::getRequestVar('idSite', 0, 'int');

        try {
            $result = API::getInstance()->getDetectedEventTypes($idSite);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return $this->jsonResponse($result);
    }

    private function jsonResponse($data): string
    {
        Common::sendHeader('Content-Type: application/json; charset=utf-8');
        return json_encode($data);
    }

    private function getAvailableIcons()
    {
        $iconsDir = __DIR__ . '/icons/material/';
        $icons = [];

        if (is_dir($iconsDir)) {
            $files = scandir($iconsDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'svg') {
                    $name = pathinfo($file, PATHINFO_FILENAME);
                    $icons[] = $name;
                }
            }
        }

        sort($icons);
        return $icons;
    }
}
