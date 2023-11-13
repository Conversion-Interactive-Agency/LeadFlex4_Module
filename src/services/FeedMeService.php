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
use yii\base\Event;
use verbb\formie\services\Integrations;
use verbb\formie\events\RegisterIntegrationsEvent;

use craft\feedme\events\FeedProcessEvent;
use craft\feedme\services\Process;

use conversionia\leadflex\helpers\EntryHelper;

class FeedMeService extends Component
{
    public function registerEvents()
    {
        Event::on(Process::class, Process::EVENT_STEP_BEFORE_PARSE_CONTENT, [$this, 'beforeParseContent']);
    }

    public function beforeParseContent(FeedProcessEvent $event)
    {
        $entry = $event->element;
        if (!EntryHelper::isJobEntry($entry)) {
            return false;
        }
        $isExistingElement = $entry->id;
        if ($isExistingElement) {
            unset($event->feed['fieldMapping']['title']);
            unset($event->feed['fieldMapping']['slug']);
            return $event;
        }
    }
}
