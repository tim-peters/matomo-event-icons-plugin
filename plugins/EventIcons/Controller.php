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

    public function icon()
    {
        Piwik::checkUserHasSuperUserAccess();

        $name = Common::getRequestVar('name', '', 'string');
        $name = preg_replace('/\.svg$/i', '', $name);
        $name = preg_replace('/[^a-z0-9_\-]/', '', $name);
        if (!$name) {
            return $this->jsonResponse(['error' => 'No icon name provided']);
        }

        $paths = $this->loadIconPaths();
        if (!isset($paths[$name])) {
            Common::sendHeader('Content-Type: image/svg+xml; charset=utf-8');
            return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" fill="#ccc"/></svg>';
        }

        Common::sendHeader('Content-Type: image/svg+xml; charset=utf-8');
        Common::sendHeader('Cache-Control: public, max-age=86400');
        return '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">' . $paths[$name] . '</svg>';
    }

    private function loadIconPaths(): array
    {
        static $paths = null;
        if ($paths !== null) {
            return $paths;
        }
        $jsonFile = __DIR__ . '/icons/material-paths.json';
        if (!file_exists($jsonFile)) {
            $paths = [];
            return $paths;
        }
        $paths = json_decode(file_get_contents($jsonFile), true) ?: [];
        return $paths;
    }

    private function getAvailableIcons(): array
    {
        $paths = $this->loadIconPaths();
        $icons = array_keys($paths);
        sort($icons);
        return $icons;
    }
}
