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

/**
 * Class PHPAgent
 */
class PHPAgent implements TransactionHandlerInterface
{
    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function addCustomParameter(string $key, $value)
    {
        return newrelic_add_custom_parameter($key, $value);
    }

    /**
     * @param bool $flag
     */
    public function backgroundJob(bool $flag = true)
    {
        newrelic_background_job($flag);
    }

    /**
     * @param bool $enableFlag
     */
    public function captureParams(bool $enableFlag = true)
    {
        newrelic_capture_params($enableFlag);
    }

    /**
     * @param string $metricName
     * @param float  $value
     *
     * @return bool
     */
    public function customMetric(string $metricName, float $value)
    {
        return newrelic_custom_metric($metricName, $value);
    }

    /**
     * @return bool
     */
    public function disableAutorum(): ?bool
    {
        return newrelic_disable_autorum();
    }

    /**
     *
     */
    public function endOfTransaction()
    {
        newrelic_end_of_transaction();
    }

    /**
     * @param bool $ignore
     *
     * @return bool
     */
    public function endTransaction(bool $ignore = false)
    {
        return newrelic_end_transaction($ignore);
    }

    /**
     *
     */
    public function ignoreApdex()
    {
        newrelic_ignore_apdex();
    }

    /**
     *
     */
    public function ignoreTransaction()
    {
        newrelic_ignore_transaction();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function nameTransaction(string $name): bool
    {
        return newrelic_name_transaction($name);
    }

    /**
     * @param string     $message
     * @param \Exception $exception
     */
    public function noticeError(string $message, \Exception $exception)
    {
        newrelic_notice_error($message, $exception);
    }

    /**
     * @param callable $func
     * @param array    $parameters
     *
     * @return mixed
     */
    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        return newrelic_record_datastore_segment($func, $parameters);
    }

    /**
     * @param string $name
     * @param string $license
     * @param bool   $xmit
     *
     * @return bool
     */
    public function setAppname(string $name, string $license = '', bool $xmit = false): bool
    {
        return newrelic_set_appname($name, $license, $xmit);
    }

    /**
     * @param string $userValue
     * @param string $accountValue
     * @param string $productValue
     *
     * @return bool
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue)
    {
        return newrelic_set_user_attributes($userValue, $accountValue, $productValue);
    }

    /**
     * @param string      $appname
     * @param string|null $license
     *
     * @return bool
     */
    public function startTransaction(string $appname, string $license = null)
    {
        return newrelic_start_transaction($appname, $license);
    }
}
