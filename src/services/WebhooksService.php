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

use conversionia\leadflex\webhooks\DriverReachFormie;
use conversionia\leadflex\webhooks\TenstreetFormie;
use conversionia\leadflex\webhooks\EbeFormie;
use conversionia\leadflex\webhooks\UkgFormie;
use conversionia\leadflex\webhooks\TalentDriverFormie;

class WebhooksService extends Component
{
    public function registerEvents()
    {
        Event::on(
            Integrations::class,
            Integrations::EVENT_REGISTER_INTEGRATIONS,
            static function(RegisterIntegrationsEvent $event) {
                $event->webhooks[] = TenstreetFormie::class;
                $event->webhooks[] = DriverReachFormie::class;
                $event->webhooks[] = EbeFormie::class;
                $event->webhooks[] = UkgFormie::class;
                $event->webhooks[] = TalentDriverFormie::class;
            }
        );
    }
}
