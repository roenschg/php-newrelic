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


namespace Groensch\NewRelic\CustomEventHandler;

use PHPUnit\Framework\TestCase;
use Groensch\NewRelic\HttpInsertApi;

/**
 * Class PHPAgentTest
 */
class PHPAgentTest extends TestCase
{
    public static $newRelicMethodCallCount = 0;

    /**
     *
     */
    public function testThrowsCustomEventIsToBigException()
    {
        // Eval because this will never fit to the coding style requirements
        eval('function newrelic_record_custom_event()
        {
            ++Groensch\NewRelic\CustomEventHandler\PHPAgentTest::$newRelicMethodCallCount;
        }');

        $data = ['test' => "test"];

        $instance = new PHPAgent();

        $instance->recordCustomEvent('test', $data);
        $this->assertEquals(1, self::$newRelicMethodCallCount);
    }
}
