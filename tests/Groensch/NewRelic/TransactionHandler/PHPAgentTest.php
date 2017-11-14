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

namespace Groensch\NewRelic\TransactionHandler;

use PHPUnit\Framework\TestCase;

/**
 * Class PHPAgentTest
 */
class PHPAgentTest extends TestCase
{
    /** @var PHPAgent */
    private static $instance = null;
    /**
     *
     */
    public static $functionMockCallCount = [];

    /**
     *
     */
    public function setUp()
    {
        self::$instance = new PHPAgent();

        parent::setUp();
    }

    /**
     * @return array
     */
    public function newRelicFunctionWasCalledProvider(): array
    {
        $anonFunction = function () {
        };

        return [
            ['addCustomParameter',     'newrelic_add_custom_parameter',     ['key', 1], true],
            ['backgroundJob',          'newrelic_background_job',           []],
            ['captureParams',          'newrelic_capture_params',           [false]],
            ['customMetric',           'newrelic_custom_metric',            ['metric_name', 1], true],
            ['disableAutorum',         'newrelic_disable_autorum',          [], true],
            ['endOfTransaction',       'newrelic_end_of_transaction',       []],
            ['endTransaction',         'newrelic_end_transaction',          [true], true],
            ['ignoreApdex',            'newrelic_ignore_apdex',             []],
            ['ignoreTransaction',      'newrelic_ignore_transaction',       []],
            ['nameTransaction',        'newrelic_name_transaction',         ['name'], true],
            ['noticeError',            'newrelic_notice_error',             ['message', new \Exception()]],
            ['recordDatastoreSegment', 'newrelic_record_datastore_segment', [$anonFunction, []], true],
            ['setAppname',             'newrelic_set_appname',              ['name', 'license', false], true],
            ['setUserAttributes',      'newrelic_set_user_attributes',      ['user_value', 'account_value', 'product_value'], true],
            ['startTransaction',       'newrelic_start_transaction',        ['appname', 'license'], true],
        ];
    }

    /**
     * @dataProvider newRelicFunctionWasCalledProvider
     *
     * @param string $methodName
     * @param string $functionName
     * @param array  $parameters
     * @param mixed  $returnValue
     */
    public function testNewRelicFunctionWasCalled(string $methodName, string $functionName, array $parameters, $returnValue = null)
    {
        $this->defineFunctionMock($functionName, $returnValue);

        call_user_func_array(array(self::$instance, $methodName), $parameters);

        $this->assertFunctionMockWasCalledNTimes($functionName, 1);
    }

    /**
     * @param string $name
     * @param mixed  $returnValue
     */
    private function defineFunctionMock(string $name, $returnValue)
    {
        self::$functionMockCallCount[$name] = 0;

        // Eval because this will never fit to the coding style requirements
        eval(sprintf(
            'function %s(){++%s::$functionMockCallCount["%s"]; return %s;}',
            $name,
            self::class,
            $name,
            var_export($returnValue, true)
        ));
    }

    /**
     * @param string $name
     * @param int    $n
     */
    private function assertFunctionMockWasCalledNTimes(string $name, int $n)
    {
        $this->assertEquals($n, self::$functionMockCallCount[$name]);
    }
}
