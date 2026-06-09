<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
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

        $sql = "
            SELECT
                cat.name AS category,
                act.name AS action,
                COUNT(*) AS occurrences
            FROM {log_link_visit_action} lva
            INNER JOIN {log_action} cat ON lva.idaction_event_category = cat.idaction
            INNER JOIN {log_action} act ON lva.idaction_event_action = act.idaction
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
