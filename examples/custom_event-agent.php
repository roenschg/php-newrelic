<?php
require_once 'vendor/autoload.php';

use Groensch\NewRelic\Handler as NewRelicHandler;
use Groensch\NewRelic\CustomEventHandler\PHPAgent as NewRelicCustomEventHandler;


// initalize new relic handler
$newRelic = new NewRelicHandler(new NewRelicCustomEventHandler());

// Send custom event
$newRelic->recordCustomEvent('test', [
    'recorded' => (string) time()
]);