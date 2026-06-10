<?php

namespace Piwik\Plugins\EventIcons;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu)
    {
        $menu->addSystemItem(
            'EventIcons_AdminMenuTitle',
            $this->urlForAction('index'),
            $orderId = 30
        );
    }
}
