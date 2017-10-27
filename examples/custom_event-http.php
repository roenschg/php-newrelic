<?php
require_once 'vendor/autoload.php';

use Groensch\NewRelic\Handler as NewRelicHandler;
use Groensch\NewRelic\HttpInsertApi as NewRelicHttpInsertApi;
use Groensch\NewRelic\CustomEventHandler\Http as NewRelicCustomEventHttpHandler;

const NEW_RELIC_API_ACCOUNT_ID = -1;
const NEW_RELIC_API_INSERT_KEY = '';

// Configure NewRelic handler
$newRelicHttpApi = new NewRelicHttpInsertApi(NEW_RELIC_API_ACCOUNT_ID, NEW_RELIC_API_INSERT_KEY);
$newRelicCustomEventHandler = new NewRelicCustomEventHttpHandler($newRelicHttpApi);
$newRelic = new NewRelicHandler($newRelicCustomEventHandler);

// Record custom event
$newRelic->recordCustomEvent('test', [
    'recorded' => (string) time()
]);

// Record custom event
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $newRelic->recordCustomEvent('test', [
        'recorded' => (string) time()
    ]);
}
$end = microtime(true);

printf("Sending 100 events took %1.4f seconds".PHP_EOL, ($end - $start));
