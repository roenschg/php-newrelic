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
 * Interface TransactionHandlerInterface
 */
interface TransactionHandlerInterface
{
    /**
     * @param string $key
     * @param scalar $value
     *
     * @return mixed
     */
    public function addCustomParameter(string $key, $value);

    /**
     * @param bool $flag
     */
    public function backgroundJob(bool $flag = true);

    /**
     * @param bool $enableFlag
     */
    public function captureParams(bool $enableFlag = true);

    /**
     * @param string $metricName
     * @param float  $value
     *
     * @return mixed
     */
    public function customMetric(string $metricName, float $value);

    /**
     * @return bool
     */
    public function disableAutorum(): bool;

    /**
     *
     */
    public function endOfTransaction();

    /**
     * @param bool $ignore
     *
     * @return mixed
     */
    public function endTransaction(bool $ignore = false);

    /**
     *
     */
    public function ignoreApdex();

    /**
     *
     */
    public function ignoreTransaction();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function nameTransaction(string $name): bool;

    /**
     * @param string     $message
     * @param \Exception $exception
     */
    public function noticeError(string $message, \Exception $exception);

    /**
     * @param callable $func
     * @param array    $parameters
     *
     * @return mixed
     */
    public function recordDatastoreSegment(callable $func, array $parameters);

    /**
     * @param string $name
     * @param string $license
     * @param bool   $xmit
     *
     * @return bool
     */
    public function setAppname(string $name, string $license = '', bool $xmit = false): bool;

    /**
     * @param string $userValue
     * @param string $accountValue
     * @param string $productValue
     *
     * @return mixed
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue);

    /**
     * @param string      $appname
     * @param string|null $license
     *
     * @return mixed
     */
    public function startTransaction(string $appname, string $license = null);
}
