<?php
/**
 * MIT License
 *
 * Copyright (c) 2017 Gerd Rönsch
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Groensch\NewRelic\CustomEventHandler;

use PHPUnit\Framework\TestCase;
use Groensch\NewRelic\HttpInsertApi;

/**
 * Class AutoBulkHttpTest
 */
class AutoBulkHttpTest extends TestCase
{
    /**
     *
     */
    public function testRecordCustomEventOneEventWithFlushOnDestruct(): void
    {
        $httpInsertApiMock = $this
            ->getMockBuilder(HttpInsertApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendCustomEvents'])
            ->getMock();

        $httpInsertApiMock
            ->expects($this->once())
            ->method('sendCustomEvents')
            ->with(sprintf(
                '[{"eventType":"moneyTransfer","timestamp":%d,"from":"Russia","to":"Saudi Arabia"}]',
                time()
            ));

        $instance = new AutoBulkHttp($httpInsertApiMock);

        $instance->recordCustomEvent('moneyTransfer', [
            'from' => 'Russia',
            'to' => 'Saudi Arabia',
        ]);

        // Buffer will be flushed here
        unset($instance);
    }

    /**
     * Make sure
     */
    public function testRecordCustomEventMultibleEventsWithFlushOnDestruct(): void
    {
        $httpInsertApiMock = $this
            ->getMockBuilder(HttpInsertApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendCustomEvents'])
            ->getMock();

        $httpInsertApiMock
            ->expects($this->once())
            ->method('sendCustomEvents')
            ->with(sprintf(
                '[{"eventType":"moneyTransfer","timestamp":%d,"from":"Russia","to":"Saudi Arabia"},{"eventType":"delivery","timestamp":%d,"warnings":"Explosives"}]',
                time(),
                time()
            ));

        $instance = new AutoBulkHttp($httpInsertApiMock);

        $instance->recordCustomEvent('moneyTransfer', [
            'from' => 'Russia',
            'to' => 'Saudi Arabia',
        ]);
        $instance->recordCustomEvent('delivery', [
            'warnings' => 'Explosives',
        ]);

        // Buffer will be flushed here
        unset($instance);
    }

    /**
     * @expectedException \Groensch\NewRelic\CustomEventIsToBigException
     */
    public function testThrowsCustomEventIsToBigException(): void
    {
        $data = ['test' => str_repeat(".", (1024*1024)+1)];

        $httpInsertApiMock = $this
            ->getMockBuilder(HttpInsertApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $instance = new AutoBulkHttp($httpInsertApiMock);

        $instance->recordCustomEvent('test', $data);
    }

    /**
     * Test if the buffer is flushed as soon as there are more than 1000 elements
     */
    public function testAutoFlushBufferIfMaximumCountOfEventsIsReached()
    {
        $httpInsertApiMock = $this
            ->getMockBuilder(HttpInsertApi::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendCustomEvents'])
            ->getMock();

        $httpInsertApiMock
            ->expects($this->exactly(2))
            ->method('sendCustomEvents');

        $instance = new AutoBulkHttp($httpInsertApiMock);

        for ($i = 1; $i <= 1001; ++$i) {
            $instance->recordCustomEvent('sackOfRiceHasBeenFallen', [
                'where' => 'China',
            ]);
        }
    }

    /**
     * Test if it buffer is flushed before an custom event is added which would make the request to the API to big (>1MB)
     */
    public function testAutoFlushBufferIfSizeLimitIsReached()
    {
        $eventsPerFlushCount = 3;
        $flushCount = 5;
        $additionCharacterForEachCustomEvent = 30 + strlen('"timestamp:"'.time().',');
        $additionCharacterForTheRequestCount = 2;
        $flushThreshold = 1048576;
        $size = (int) floor(($flushThreshold - $additionCharacterForTheRequestCount - ($eventsPerFlushCount * $additionCharacterForEachCustomEvent)) / $eventsPerFlushCount); // 1048604
        $data = ['test' => str_repeat(".", $size)];

        $httpInsertApiMock = $this
            ->getMockBuilder(HttpInsertApi::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpInsertApiMock
            ->expects($this->exactly($flushCount))
            ->method('sendCustomEvents');

        $instance = new AutoBulkHttp($httpInsertApiMock);

        for ($flushIterator = 0; $flushIterator < $flushCount; ++$flushIterator) {
            for ($eventIterator = 0; $eventIterator < $eventsPerFlushCount; ++$eventIterator) {
                $instance->recordCustomEvent('test', $data);
            }
        }
    }
}
