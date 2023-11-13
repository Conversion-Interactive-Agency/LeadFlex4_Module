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

use craft\base\Component;

use verbb\formie\services\Integrations;
use verbb\formie\events\RegisterIntegrationsEvent;

use conversionia\leadflex\helpers\EntryHelper;

use yii\base\Event;
use craft\errors\ElementNotFoundException;
use craft\events\ModelEvent;
use craft\elements\Entry;
use craft\base\Element;
use craft\helpers\StringHelper;


class EntryService extends Component
{
    public function registerEvents()
    {
        Event::on(Entry::class, Element::EVENT_BEFORE_SAVE, [$this, 'entryBeforeSave']);
        Event::on(Entry::class, Element::EVENT_AFTER_SAVE, [$this, 'entryAfterSave']);
    }

    function entryBeforeSave(ModelEvent $event)
    {
        $entry = $event->sender;
        $fields = ['location','statewideJob','advertiseJob','assignedCampaign'];
        if (!EntryHelper::doFieldsExists($entry, $fields)) {
            return;
        }

        $assignedCampaign = $entry->getFieldValue('assignedCampaign')->one();
        if(!$entry->enabled || is_null($assignedCampaign)){
            $event->sender->setFieldValue('advertiseJob', 'false');
            $event->sender->setFieldValue('assignedCampaign', []);
        }

        $location = $entry->getFieldValue('location');
        $isStatewide = empty($location['city']);
        $event->sender->setFieldValue('statewideJob', $isStatewide);
    }

    /**
     * @throws Exception
     * @throws \Throwable
     * @throws ElementNotFoundException
     */
    function entryAfterSave(ModelEvent $event)
    {
        $entry = $event->sender;
        $fields = ['protectedSlug','defaultJobDescription','protectedSlug'];
        if (!EntryHelper::doFieldsExists($entry, $fields)) {
            return;
        }

        $defaultJob = $entry->getFieldValue('defaultJobDescription')->one();
        $isProtected = $entry->getFieldValue('protectedSlug');
        if (!empty($defaultJob) && !$isProtected) {
            $titleText = !empty($entry->adHeadline) ? $entry->adHeadline
                : (!empty($defaultJob->adHeadline) ? $defaultJob->adHeadline : $defaultJob->title);
            $title = StringHelper::slugify($titleText);
            $entry->slug = $title . "-" . $entry->id;
            $entry->setFieldValue('protectedSlug', true);
            Craft::$app->elements->saveElement($entry);
        }
    }
}
