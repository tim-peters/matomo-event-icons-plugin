<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\API as PluginApi;
use Piwik\Plugins\EventIcons\Settings\SystemSettings;

class API extends PluginApi
{
    private const DAYS_BACK = 90;
    private const MAX_RESULTS = 500;

    public function getDetectedEventTypes(int $idSite): array
    {
        if ($idSite <= 0) {
            return [];
        }

        $dateFrom = Date::now()->subDay(self::DAYS_BACK)->toString();
        $dateTo = Date::now()->toString();

        $logLinkVisitAction = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $sql = "
            SELECT
                cat.name AS category,
                act.name AS action,
                COUNT(*) AS occurrences
            FROM $logLinkVisitAction lva
            INNER JOIN $logAction cat ON lva.idaction_event_category = cat.idaction
            INNER JOIN $logAction act ON lva.idaction_event_action = act.idaction
            WHERE lva.server_time >= ?
              AND lva.server_time <= ?
              AND lva.idsite = ?
            GROUP BY cat.name, act.name
            ORDER BY occurrences DESC
            LIMIT " . self::MAX_RESULTS . "
        ";

        $rows = Db::fetchAll($sql, [
            $dateFrom . ' 00:00:00',
            $dateTo . ' 23:59:59',
            $idSite,
        ]);

        $events = [];
        foreach ($rows as $row) {
            $events[] = [
                'category' => Common::unsanitizeInputValue($row['category']),
                'action' => Common::unsanitizeInputValue($row['action']),
                'occurrences' => (int) $row['occurrences'],
                'key' => Common::unsanitizeInputValue($row['category']) . '/' . Common::unsanitizeInputValue($row['action']),
            ];
        }

        return $events;
    }

    public function getVisitActionTimes(int $idVisit, int $idSite): array
    {
        Piwik::checkUserHasViewAccess($idSite);

        if ($idVisit <= 0) {
            return [];
        }

        try {
            $sql = "SELECT server_time, time_spent_ref_action
                    FROM " . Common::prefixTable('log_link_visit_action') . "
                    WHERE idvisit = ?
                    ORDER BY server_time ASC";
            $rows = Db::fetchAll($sql, [$idVisit]);

            $actions = [];
            foreach ($rows as $row) {
                $timeSpent = $row['time_spent_ref_action'];
                $timeSpentInt = (int)$timeSpent;
                $actions[] = [
                    'serverTimePretty' => date('H:i:s', strtotime($row['server_time'])),
                    'timeSpentPretty' => $timeSpentInt ? $this->formatTimeSpent($timeSpentInt) : null,
                    'timeSpentSeconds' => $timeSpentInt ?: null,
                ];
            }

            return $actions;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function formatTimeSpent(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }
        $minutes = intdiv($seconds, 60);
        $secs = $seconds % 60;
        if ($minutes < 60) {
            return $minutes . ' min' . ($secs ? ' ' . $secs . 's' : '');
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return $hours . 'h ' . $mins . ' min';
    }

    public function debugTest(int $idSite): array
    {
        $logLinkVisitAction = Common::prefixTable('log_link_visit_action');
        $logAction = Common::prefixTable('log_action');

        $sql = "
            SELECT
                cat.name AS category,
                act.name AS action,
                COUNT(*) AS occurrences
            FROM $logLinkVisitAction lva
            INNER JOIN $logAction cat ON lva.idaction_event_category = cat.idaction
            INNER JOIN $logAction act ON lva.idaction_event_action = act.idaction
            WHERE lva.idsite = ?
            GROUP BY cat.name, act.name
            ORDER BY occurrences DESC
            LIMIT " . self::MAX_RESULTS . "
        ";

        try {
            $rows = Db::fetchAll($sql, [(int)$idSite]);
            return ['success' => true, 'count' => count($rows), 'data' => $rows];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage(), 'sql' => $sql];
        }
    }

    public function getIconMapping(): array
    {
        $settings = new SystemSettings();
        $mapping = $settings->eventIconMapping->getValue();
        return $mapping ?: [];
    }

    public function setIconMapping(array $mapping): array
    {
        $valid = [];
        foreach ($mapping as $entry) {
            if (!empty($entry['key']) && !empty($entry['icon'])) {
                $valid[] = [
                    'key' => Common::sanitizeInputValue($entry['key']),
                    'icon' => preg_replace('/[^a-z0-9_\-]/', '', $entry['icon']),
                ];
            }
        }

        $settings = new SystemSettings();
        $settings->eventIconMapping->setValue($valid);
        $settings->save();

        return ['success' => true, 'count' => count($valid)];
    }

    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
