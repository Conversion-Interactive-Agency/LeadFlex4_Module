<?php
namespace conversionia\leadflex;

use Craft;

use yii\base\Event;
use yii\base\Exception;
use yii\base\Module;
use yii\base\InvalidConfigException;

use verbb\formie\events\RegisterIntegrationsEvent;
use verbb\formie\services\Integrations;

use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;

use craft\errors\ElementNotFoundException;
use craft\elements\Entry;
use craft\base\Element;
use craft\events\ModelEvent;
use craft\events\RegisterElementExportersEvent;
use craft\helpers\StringHelper;
use craft\helpers\ElementHelper;

class LeadFlex extends Module
{
    public $key = 'jobs';
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

        $request = Craft::$app->getRequest();

        // Adjust controller namespace for console requests
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'conversionia\leadflex\console\controllers';
        } else {
            if ($request->getIsCpRequest()) {
                Craft::$app->view->registerAssetBundle(\conversionia\leadflex\assets\ControlPanel::class);
                $this->_registerExporters();
            }
        }
        $this->_registerFormieIntegrations();
        Event::on(Entry::class, Element::EVENT_BEFORE_SAVE, [$this, 'entryBeforeSave']);
    }

    function entryBeforeSave(ModelEvent $event)
    {
        $entry = $event->sender;
        $handle = strtolower($entry->section->handle);
        $validated = $handle === $this->key;

        if (!$validated) {
            return;
        }
        // Statewide field toggle
        $location = $entry->getFieldValue('location');
        $isStatewide = empty($location['city']);
        $event->sender->setFieldValue('statewideJob', $isStatewide);

        // Slug Manipulation On first save
        $defaultJob = $entry->getFieldValue('defaultJobDescription')->one();
        if (!empty($defaultJob) && ElementHelper::isTempSlug($entry->slug)) {
            $titleText = !empty($entry->adHeadline) ? $entry->adHeadline
                : (!empty($defaultJob->adHeadline) ? $defaultJob->adHeadline : $defaultJob->title);
            $entry->slug = StringHelper::slugify($titleText);
        }
    }

    /**
     * Register custom webhook for Formie.
     */
    private function _registerFormieIntegrations()
    {
        Event::on(
            Integrations::class,
            Integrations::EVENT_REGISTER_INTEGRATIONS,
            static function(RegisterIntegrationsEvent $event) {
                $event->webhooks[] = \conversionia\leadflex\webhooks\DriverReachFormie::class;
                $event->webhooks[] = \conversionia\leadflex\webhooks\TenstreetFormie::class;
                $event->webhooks[] = \conversionia\leadflex\webhooks\EbeFormie::class;
                $event->webhooks[] = \conversionia\leadflex\webhooks\UkgFormie::class;
            }
        );
    }

    private function _registerExporters()
    {
        // Register exporters
        Event::on(
            Entry::class,
            Element::EVENT_REGISTER_EXPORTERS,
            static function (RegisterElementExportersEvent $event) {
                $event->exporters[] = \conversionia\leadflex\exporters\GeosheetExporter::class;
            }
        );
    }
}
