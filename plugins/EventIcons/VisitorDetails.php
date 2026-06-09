<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Plugins\Live\VisitorDetailsAbstract;

class VisitorDetails extends VisitorDetailsAbstract
{
    public function extendActionDetails(&$action, $nextAction, $visitorDetails)
    {
        if (!empty($action['eventType'])) {
            $category = $action['eventCategory'] ?? '';
            $actionName = $action['eventAction'] ?? '';
            if ($category && $actionName) {
                $action['eventIconKey'] = $category . '/' . $actionName;
            }
        }
    }
}
