<?php
require_once 'vendor/autoload.php';

use Groensch\NewRelic\Handler as NewRelicHandler;
use Groensch\NewRelic\HttpInsertApi as NewRelicHttpInsertApi;
use Groensch\NewRelic\CustomEventHandler\AutoBulkHttp as NewRelicCustomEventBulkHttpHandler;

const NEW_RELIC_API_ACCOUNT_ID = -1;
const NEW_RELIC_API_INSERT_KEY = '';

// Configure NewRelic handler
$newRelicHttpApi = new NewRelicHttpInsertApi(NEW_RELIC_API_ACCOUNT_ID, NEW_RELIC_API_INSERT_KEY);
$newRelicCustomEventHandler = new NewRelicCustomEventBulkHttpHandler($newRelicHttpApi);
$newRelic = new NewRelicHandler($newRelicCustomEventHandler);

// Record custom event
$newRelic->recordCustomEvent('test', [
    'recorded' => (string) time()
]);
