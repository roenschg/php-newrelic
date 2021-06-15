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

namespace Groensch\NewRelic\TransactionHandler;

/**
 */
class CrossedFingers implements TransactionHandlerInterface
{
    /** @var TransactionHandlerInterface */
    private $handler;

    /**
     * CrossedFingers constructor.
     * @param TransactionHandlerInterface|null $handler
     */
    public function __construct(?TransactionHandlerInterface $handler = null)
    {
        if (null === $handler) {
            $this->handler = new PHPAgent();
        }

        $this->handler = $handler;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function addCustomParameter(string $key, $value)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->addCustomParameter($key, $value);
    }

    /**
     * @param bool $flag
     */
    public function backgroundJob(bool $flag = true)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->backgroundJob($flag);
    }

    /**
     * @param bool $enableFlag
     */
    public function captureParams(bool $enableFlag = true)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->captureParams($enableFlag);
    }

    /**
     * @param string $metricName
     * @param float  $value
     *
     * @return void
     */
    public function customMetric(string $metricName, float $value)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->customMetric($metricName, $value);
    }

    /**
     * @return bool
     */
    public function disableAutorum(): ?bool
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return true;
        }

        return $this->handler->disableAutorum();
    }

    /**
     *
     */
    public function endOfTransaction()
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->endOfTransaction();
    }

    /**
     * @param bool $ignore
     *
     * @return void
     */
    public function endTransaction(bool $ignore = false)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->endTransaction($ignore);
    }

    /**
     *
     */
    public function ignoreApdex()
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->ignoreApdex();
    }

    /**
     *
     */
    public function ignoreTransaction()
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->ignoreTransaction();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function nameTransaction(string $name): bool
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return true;
        }

        return $this->handler->nameTransaction($name);
    }

    /**
     * @param string     $message
     * @param \Exception $exception
     */
    public function noticeError(string $message, \Exception $exception)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->noticeError($message, $exception);
    }

    /**
     * @param callable $func
     * @param array    $parameters
     *
     * @return void
     */
    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->recordDatastoreSegment($func, $parameters);
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
        if (!$this->isNewrelicExtensionLoaded()) {
            return true;
        }

        return $this->handler->setAppname($name, $license, $xmit);
    }

    /**
     * @param string $userValue
     * @param string $accountValue
     * @param string $productValue
     *
     * @return void
     */
    public function setUserAttributes(string $userValue, string $accountValue, string $productValue)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->setUserAttributes($userValue, $accountValue, $productValue);
    }

    /**
     * @param string      $appname
     * @param string|null $license
     *
     * @return void
     */
    public function startTransaction(string $appname, string $license = null)
    {
        if (!$this->isNewrelicExtensionLoaded()) {
            return;
        }

        $this->handler->startTransaction($appname, $license);
    }

    /**
     * @return bool
     */
    private function isNewrelicExtensionLoaded(): bool
    {
        return extension_loaded('newrelic');
    }
}
