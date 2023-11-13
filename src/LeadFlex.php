<?php
namespace conversionia\leadflex;

use Craft;

use conversionia\leadflex\services\WebhooksService;
use conversionia\leadflex\services\ExportsService;
use conversionia\leadflex\services\EntryService;
use conversionia\leadflex\services\FeedmeService;
use conversionia\leadflex\services\ControlPanelService;

use conversionia\leadflex\assets\ControlPanel;

use yii\base\Module;


use craft\elements\Entry;
use craft\base\Element;
use craft\events\ModelEvent;

use craft\helpers\StringHelper;

class LeadFlex extends Module
{
    public $section = 'jobs';
    /**
     * @var string
     */
    public $controllerNamespace;

    /**
     * Initializes the plugin.
     */
    public function init()
    {
        parent::init();

        // Set alias for this module
        Craft::setAlias('@conversionia', __DIR__);

        // Register our services
        $this->setComponents([
            'controlpanel' => ControlPanel::class,
            'entry' => EntryService::class,
            'exports' => ExportsService::class,
            'feedme' => FeedmeService::class,
            'webhooks' => WebhooksService::class,
        ]);

        // Register Events
        $request = Craft::$app->getRequest();
        // Adjust controller namespace for console requests
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'conversionia\leadflex\console\controllers';
            $this->entry->registerEvents();
            $this->feedme->registerEvents();
        }

        if ($request->getIsCpRequest()) {
            $this->controlpanel->init();
            $this->exports->registerEvents();
            $this->webhooks->registerEvents();
        }
    }
}
