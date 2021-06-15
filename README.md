# php-newrelic
This package provides a simple and clean wrapper around the newrelic APM agent API and also enables you to send large
amounts of custom metrics into newrelic without dropping any data.

## Installation
```
composer require groensch/php-newrelic
```

## Basic Usage
You have one class that acts as a wrapper for every function that the PHP agent api defines.

```php
$newRelic = new Groensch\NewRelic\Handler(new Groensch\NewRelic\CustomEventHandler\PHPAgent());

$newRelic->nameTransaction('MyCustomTransactionName');
$newRelic->recordCustomEvent('eventName', ['count' => 20]);
```

## Crossed fingers (Instead of using extension_loaded('newrelic'))
The `CrossedFingers` handler allows you to use the same methods on environments without a NewRelic extension installed
without throwing errors. So instead of doing this if on every line where you want to send events to:
```php
if (extension_loaded('newrelic')) { // Ensure PHP agent is available
    newrelic_name_transaction("/Product/view/");
}
```

```php
$newRelic = new Groensch\NewRelic\Handler(
    new \Groensch\NewRelic\CustomEventHandler\CrossedFingers(),
    new \Groensch\NewRelic\TransactionHandler\CrossedFingers()
);

$newRelic->nameTransaction('MyCustomTransactionName');
$newRelic->recordCustomEvent('eventName', ['count' => 20]);
```

If the extension does not exist, nothing will be done.
 
## HTTP Bulk sending (Send huge amounts of custom events reliable)
If you need to make sure that you send all the data to NewRelic even if you reach the limits
of the agent you can use the CustomEventHandler `AutoBulkHttp`.

Please note that this means you application will be slowed down by the amount of requests you
send to NewRelic because of the logged custom events.

The following code shows you how to setup the NewRelicHandler to send the CustomEvents over the
API and automaticly pack requests together in bulks.

```php
use Groensch\NewRelic\Handler as NewRelicHandler;
use Groensch\NewRelic\HttpInsertApi as NewRelicHttpInsertApi;
use Groensch\NewRelic\CustomEventHandler\AutoBulkHttp as NewRelicCustomEventBulkHttpHandler;
    
// Configure NewRelic handler
$newRelicHttpApi = new NewRelicHttpInsertApi(NEW_RELIC_API_ACCOUNT_ID, NEW_RELIC_API_INSERT_KEY);
$newRelicCustomEventHandler = new NewRelicCustomEventBulkHttpHandler($newRelicHttpApi);
$newRelic = new NewRelicHandler($newRelicCustomEventHandler);
    
// Record custom event
$newRelic->recordCustomEvent('test1', ['recorded' => (string) time()]);
$newRelic->recordCustomEvent('test2', ['recorded' => (string) time()]);
```

The `NewRelicHttpInsertApi` is responsible to connect to the InsightAPI and the `NewRelicCustomEventBulkHttpHandler`
is responsible to pack custom events together in bulk and is aware of the API limitations.

If the package is big enough it will flush the buffer and send the request to the insights api. The Buffer will be cleared
during shutdown of you application as well.

If the data you pass to the method `ecordCustomEvent` is invalid or to big, you will get an Exception.

## List of methods
For further information about the methods please see the 
[NewRelic documentation](https://docs.newrelic.com/docs/agents/php-agent/php-agent-api)

* recordCustomEvent(...)
* addCustomParameter(...)
* backgroundJob(...)
* captureParams(...)
* customMetric(...)
* disableAutorum(...)
* endOfTransaction(...)
* endTransaction(...)
* ignoreApdex(...)
* ignoreTransaction(...)
* nameTransaction(...)
* noticeError(...)
* recordDatastoreSegment(...)
* setAppname(...)
* setUserAttributes(...)
* startTransaction(...)
* getTransactionHandler(...)
* setTransactionHandler(...)

## Running Unit-Tests
```shell
docker-compose run php composer install
docker-compose run php vendor/bin/phpunit
```

## Running Code-Sniffer
```shell
docker-compose run php composer install
docker-compose run php vendor/bin/phpcs --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
docker-compose run php vendor/bin/phpcs
```