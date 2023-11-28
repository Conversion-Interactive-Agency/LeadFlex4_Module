<?php
namespace conversionia\leadflex\events;

use craft\events\CancelableEvent;

class ReturnJsonEvent extends CancelableEvent
{
    // Properties
    // =========================================================================
    public $data;
    public $form;
    public $json;
    public $submission;
    public $webhook;
}
