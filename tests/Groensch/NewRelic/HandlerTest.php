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

namespace Groensch\NewRelic;

use Groensch\NewRelic\TransactionHandler\PHPAgent;
use PHPUnit\Framework\TestCase;
use Groensch\NewRelic\CustomEventHandler\CustomEventHandlerInterface;

/**
 * Class HandlerTest
 */
class HandlerTest extends TestCase
{
    /**
     *
     */
    public function testCustomEventHandlerRecordCustomEventIsCalled(): void
    {
        $eventName = 'test';
        $data = ['attribute' => 'value'];

        $eventHandlerMock = $this
            ->getMockBuilder(CustomEventHandlerInterface::class)
            ->setMethods(['recordCustomEvent'])
            ->getMock()
        ;

        $eventHandlerMock
            ->expects($this->once())
            ->method('recordCustomEvent')
            ->with($eventName, $data)
        ;

        $instance = new Handler($eventHandlerMock);

        $instance->recordCustomEvent($eventName, $data);
    }

    /**
     *
     */
    public function testTruncateAttributeDataThatIsToLong(): void
    {
        $eventName = 'test';
        $dataGiven = ['attribute' => str_repeat('.', 4097)];
        $dataTruncated = ['attribute' => str_repeat('.', 4096)];

        $eventHandlerMock = $this
            ->getMockBuilder(CustomEventHandlerInterface::class)
            ->setMethods(['recordCustomEvent'])
            ->getMock()
        ;

        $eventHandlerMock
            ->expects($this->once())
            ->method('recordCustomEvent')
            ->with($eventName, $dataTruncated)
        ;

        $instance = new Handler($eventHandlerMock);

        $instance->recordCustomEvent($eventName, $dataGiven);
    }

    /**
     * @expectedException \Groensch\NewRelic\ToManyAttributesForCustomEventException
     */
    public function testThrowExceptionIfThereAreToManyAttributes(): void
    {
        $instance = new Handler($this->getMockBuilder(CustomEventHandlerInterface::class)->getMock());

        $data = [];
        for ($i = 0; $i < 256; ++$i) {
            $data['attribute_'.$i] = rand();
        }

        $instance->recordCustomEvent('test', $data);
    }

    /**
     * @expectedException \Groensch\NewRelic\NumericAttributeNameInEventException
     */
    public function testThrowExceptionIfThereIsANumericAttributeName(): void
    {
        $instance = new Handler($this->getMockBuilder(CustomEventHandlerInterface::class)->getMock());

        $data = [
            '123' => 'wrong!!!',
        ];

        $instance->recordCustomEvent('test', $data);
    }

    public function transactionHandlerIsCalledProvider(): array
    {
        $anonFunction = function () {
        };

        return [
            ['addCustomParameter', ['key', 'value'], true],
            ['backgroundJob', [false], null],
            ['captureParams', [true], null],
            ['customMetric', ['metricName', 1.2], true],
            ['disableAutorum', [], true],
            ['endOfTransaction', [], null],
            ['endTransaction', [false], true],
            ['ignoreApdex', [], null],
            ['ignoreTransaction', [], null],
            ['nameTransaction', ['name'], true],
            ['noticeError', ['message', new \Exception()], null],
            ['recordDatastoreSegment', [$anonFunction, ['test']], true],
            ['setAppname', ['name', 'license', false], false],
            ['setUserAttributes', ['userValue', 'AccountValue', 'productValue'], true],
            ['startTransaction', ['appname', 'license'], true],
        ];
    }

    /**
     * @dataProvider transactionHandlerIsCalledProvider
     *
     * @param string $methodName
     * @param array $parameters
     * @param $expectedReturnValue
     */
    public function testTransactionHandlerIsCalled(
        string $methodName,
        array $parameters,
        $expectedReturnValue
    ) {
        $transactionHandlerName = $this
            ->getMockBuilder(PHPAgent::class)
            ->setMethods([$methodName])
            ->getMock()
        ;

        $transactionHandlerName
            ->expects($this->once())
            ->method($methodName)
            ->willReturn($expectedReturnValue)
            ->withConsecutive($parameters);

        $instance = new Handler(
            $this->getMockBuilder(CustomEventHandlerInterface::class)->getMock(),
            $transactionHandlerName
        );

        $this->assertEquals($expectedReturnValue, call_user_func_array([$instance, $methodName], $parameters));
    }
}
