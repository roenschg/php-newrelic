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




namespace Groensch\NewRelic;

/**
 * Class HttpInsertApiTest
 */
class HttpInsertApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testSendCustomEvent()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct'])
            ->getMock();

        $curlMock
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $curlMock
            ->method('getInfo')
            ->willReturn([
                'http_code' => 200,
            ]);

        $instance = new HttpInsertApi(0, '', null, $curlMock);
        $instance->setCurlHandler($curlMock);
        $instance->sendCustomEvents('eventName', ['atttribute' => 'data']);
    }

    /**
     *
     */
    public function testRetryAlways500StatusCode()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct'])
            ->getMock();

        $curlMock
            ->expects($this->exactly(5))
            ->method('execute')
            ->willReturn(true);

        $curlMock
            ->method('getInfo')
            ->willReturn([
                'http_code' => 500,
            ]);

        $errorHandlerMock = $this
            ->getMockBuilder('object')
            ->setMethods(['__invoke'])
            ->getMock()
        ;

        $errorHandlerMock
            ->expects($this->once())
            ->method('__invoke')
        ;

        $instance = new HttpInsertApi(0, '', $errorHandlerMock, $curlMock);
        $instance->setCurlHandler($curlMock);
        $instance->sendCustomEvents('eventName', ['atttribute' => 'data']);
    }

    /**
     *
     */
    public function testNoRetry400StatusCode()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct'])
            ->getMock();

        $curlMock
            ->expects($this->exactly(1))
            ->method('execute')
            ->willReturn(true);

        $curlMock
            ->method('getInfo')
            ->willReturn([
                'http_code' => 400,
            ]);

        $errorHandlerMock = $this
            ->getMockBuilder('object')
            ->setMethods(['__invoke'])
            ->getMock()
        ;

        $errorHandlerMock
            ->expects($this->once())
            ->method('__invoke')
        ;

        $instance = new HttpInsertApi(0, '', $errorHandlerMock, $curlMock);
        $instance->setCurlHandler($curlMock);
        $instance->sendCustomEvents('eventName', ['atttribute' => 'data']);
    }

    /**
     *
     */
    public function testNoRetry300StatusCode()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct'])
            ->getMock();

        $curlMock
            ->expects($this->exactly(1))
            ->method('execute')
            ->willReturn(true);

        $curlMock
            ->method('getInfo')
            ->willReturn([
                'http_code' => 300,
            ]);

        $errorHandlerMock = $this
            ->getMockBuilder('object')
            ->setMethods(['__invoke'])
            ->getMock()
        ;

        $errorHandlerMock
            ->expects($this->once())
            ->method('__invoke')
        ;

        $instance = new HttpInsertApi(0, '', $errorHandlerMock, $curlMock);
        $instance->setCurlHandler($curlMock);
        $instance->sendCustomEvents('eventName', ['atttribute' => 'data']);
    }

    /**
     *
     */
    public function testNoRetryUnkownStatusCode()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct'])
            ->getMock();

        $curlMock
            ->expects($this->exactly(1))
            ->method('execute')
            ->willReturn(true);

        $curlMock
            ->method('getInfo')
            ->willReturn([
                'http_code' => 999,
            ]);

        $errorHandlerMock = $this
            ->getMockBuilder('object')
            ->setMethods(['__invoke'])
            ->getMock()
        ;

        $errorHandlerMock
            ->expects($this->once())
            ->method('__invoke')
        ;

        $instance = new HttpInsertApi(0, '', $errorHandlerMock, $curlMock);
        $instance->setCurlHandler($curlMock);
        $instance->sendCustomEvents('eventName', ['atttribute' => 'data']);
    }

    /**
     */
    public function testCurlError()
    {
        $curlMock = $this
            ->getMockBuilder(CurlWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOptionsArray', 'execute', 'getInfo', '__destruct', 'getError', 'getErrno'])
            ->getMock()
        ;

        $curlMock
            ->method('execute')
            ->willReturn(false)
        ;

        $curlMock
            ->expects($this->once())
            ->method('getError')
            ->willReturn('SSL read: error:000000')
        ;

        $curlMock
            ->expects($this->once())
            ->method('getErrno')
            ->willReturn(104)
        ;

        $errorHandlerMock = $this
            ->getMockBuilder('object')
            ->setMethods(['__invoke'])
            ->getMock()
        ;

        $errorHandlerMock
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                "Curl request to NewRelic api was not successfull. Curl errno: 0, Curl error: '104'",
                'https://insights-collector.newrelic.com/v1/accounts/0/events',
                'payload'
            )
        ;

        $instance = new HttpInsertApi(0, '', $errorHandlerMock, $curlMock);
        $instance->sendCustomEvents('payload');
    }
}
