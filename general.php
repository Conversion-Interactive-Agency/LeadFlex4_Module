<?php
use craft\helpers\App;

return [
    // Global settings
    '*' => [
        'cpTrigger'   => 'cp',
        'securityKey' => App::env('SECURITY_KEY'),

        'devMode'               => App::env('DEV_MODE') ?: 0,
        'allowUpdates'          => App::env('ALLOW_UPDATES') ?: 0,
        'allowAdminChanges'     => App::env('ALLOW_ADMIN_CHANGES') ?: 0,
        'allowTrackingScripts'  => App::env('ALLOW_TRACKING_SCRIPTS') ?: 0,
        'enableTemplateCaching' => App::env('ENABLE_TEMPLATE_CACHING') ?: 0,
        'disallowRobots'        => App::env('DISALLOW_ROBOTS') ?: 0, // Prevent robots by default

        'testToEmailAddress'    => App::env('TEST_EMAIL') ?: 'digital-services@conversionia.com',

        'autosaveDrafts'        => false,
        'omitScriptNameInUrls'  => true,
        'enableGql'             => true,

        'limitAutoSlugsToAscii'   => true,
        'convertFilenamesToAscii' => true,

        'cacheDuration'      => 'P30D', // Cache for 30 days
        'softDeleteDuration' => 'P1Y', // Keep soft-deleted items for 1 year

        'userSessionDuration'           => 'P1D', // Stay logged in for 1 day
        'rememberedUserSessionDuration' => 'P1Y', // Stay logged in for 1 year ("Remember Me")
        'verificationCodeDuration'      => 'P2W', // Verification codes expire after 2 weeks

        'defaultSearchTermOptions' => ['subLeft' => true],

        'errorTemplatePrefix' => '_errors/', // Keep error templates in this folder
        'aliases' => [
            '@businessName' => App::env('BUSINESS_NAME'),
        ],
    ],
];
