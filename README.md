[![SensioLabsInsight](https://insight.sensiolabs.com/projects/0449a463-a1d8-491a-bbc9-564d0801040b/big.png)](https://insight.sensiolabs.com/projects/0449a463-a1d8-491a-bbc9-564d0801040b)

# php-newrelic
A package that provide different functionality to send data to NewRelic

# Basic Usage
If you want to send the data with the php agent (exactly like using the PHP API from NewRelic)
you only need to create an instance and send the events like usual.
```
require_once 'vendor/autoload.php';

use Groensch\NewRelic\Handler as NewRelicHandler;
    
$newRelic = new NewRelicHandler(new NewRelicCustomEventHandler());
$newRelic->sendCustomEvent('eventName', ['count' => 20]);
```

The `NewRelicHandler` has all methods that you expect from the NewRelic api defined as 
public methods.
 
## Sending custom events via HTTP in Bulk
If you need to make sure that you send all the data to NewRelic even if you reach the limits
of the agent you can use the CustomEventHandler `AutoBulkHttp`.

Please note that this means you application will be slowed down by the amount of requests you
send to NewRelic because of the logged custom events.

The following code shows you how to setup the NewRelicHandler to send the CustomEvents over the
API and automaticly pack requests together in bulks.

```
require_once 'vendor/autoload.php';
    
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

# List of methods
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