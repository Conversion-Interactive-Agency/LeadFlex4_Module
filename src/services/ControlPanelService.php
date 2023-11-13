<?php
/**
 * Reporter plugin for Craft CMS 3.x
 *
 * CIA tool to build reports
 *
 * @link      conversionia.com
 * @copyright Copyright (c) 2023 Jeff Benusa
 */

namespace conversionia\leadflex\services;

use Craft;
use conversionia\leadflex\assets\ControlPanel;
use craft\base\Component;

class ControlPanelService extends Component
{
    public function init()
    {
        Craft::$app->view->registerAssetBundle(ControlPanel::class);
    }
}
