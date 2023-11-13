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

use yii\base\Event;
use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\RegisterElementExportersEvent;

use conversionia\leadflex\exporters\GeosheetExporter;

class ExportsService extends Component
{
    public function registerEvents()
    {
        // Register exporters
        Event::on(
            Entry::class,
            Element::EVENT_REGISTER_EXPORTERS,
            static function (RegisterElementExportersEvent $event) {
                $event->exporters[] = GeosheetExporter::class;
            }
        );
    }
}
