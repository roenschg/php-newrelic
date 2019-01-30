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

use Groensch\NewRelic\CustomEventHandler\CustomEventHandlerInterface;
use Groensch\NewRelic\TransactionHandler\TransactionHandlerInterface;
use Groensch\NewRelic\TransactionHandler\PHPAgent as TransactionHandlerPHPAgent;

/**
 * Class Handler
 */
class Handler
{
    const API_ATTRIBUTES_COUNT_MAX = 255;
    const API_ATTRIBUTE_LENGTH_MAX = 4096;

    private $customEventHandler = null;
    private $transactionHandler = null;

    /**
     * Handler constructor.
     *
     * @param CustomEventHandlerInterface      $customEventHandler
     * @param TransactionHandlerInterface|null $transactionHandler If nothing is set it will use the Groensch\NewRelic\TransactionHandler\PHPAgent
     */
    public function __construct(CustomEventHandlerInterface $customEventHandler, TransactionHandlerInterface $transactionHandler = null)
    {
        $this
            ->setCustomEventHandler($customEventHandler)
        ;

        if ($transactionHandler) {
            $this->setTransactionHandler($transactionHandler);
        } else {
            $this->setTransactionHandler(new TransactionHandlerPHPAgent());
        }
    }

    /**
     * @param string $name
     * @param array  $attributes
     */
    public function recordCustomEvent($name, $attributes)
    {
        // Throw an exception if we want to record to many attributes to an custom event
        // This happens because of a design flaw so we don´ hesitate if the program execution stops because of this
        $this->throwExceptionIfThereAreToManyAttributesForTheCustomEvent($name, $attributes);

        // We need to truncate data that is to long
        $this->truncateAttributesThatAreToLongForTheCustomEvent($attributes);

        // Check if there is no attribute name that is just a number
        $this->throwExceptionIfAnAttributeNameIsANumber($attributes);

        // Record custom event with a CustomEventHandler
        $this->getCustomEventHandler()->recordCustomEvent($name, $attributes);
    }

    /**
     * @param string $key
     * @param scalar $value
     *
     * @return mixed
     */
    public function addCustomParameter($key, $value)
    {
        return $this->getTransactionHandler()->addCustomParameter($key, $value);
    }

    /**
     * @param bool $flag
     */
    public function backgroundJob($flag = true)
    {
        $this->getTransactionHandler()->backgroundJob($flag);
    }

    /**
     * @param bool $enableFlag
     */
    public function captureParams($enableFlag = true)
    {
        $this->getTransactionHandler()->captureParams($enableFlag);
    }

    /**
     * @param string $metricName
     * @param float  $value
     *
     * @return mixed
     */
    public function customMetric($metricName, $value)
    {
        return $this->getTransactionHandler()->customMetric($metricName, $value);
    }

    /**
     * @return bool
     */
    public function disableAutorum()
    {
        return $this->getTransactionHandler()->disableAutorum();
    }

    /**
     *
     */
    public function endOfTransaction()
    {
        $this->getTransactionHandler()->endOfTransaction();
    }

    /**
     * @param bool $ignore
     *
     * @return mixed
     */
    public function endTransaction($ignore = false)
    {
        return $this->getTransactionHandler()->endTransaction($ignore);
    }

    /**
     *
     */
    public function ignoreApdex()
    {
        $this->getTransactionHandler()->ignoreApdex();
    }

    /**
     *
     */
    public function ignoreTransaction()
    {
        $this->getTransactionHandler()->ignoreTransaction();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function nameTransaction($name)
    {
        return $this->getTransactionHandler()->nameTransaction($name);
    }

    /**
     * @param string     $message
     * @param \Exception $exception
     */
    public function noticeError($message, \Exception $exception)
    {
        $this->getTransactionHandler()->noticeError($message, $exception);
    }

    /**
     * @param callable $func
     * @param array    $parameters
     *
     * @return mixed
     */
    public function recordDatastoreSegment(callable $func, array $parameters)
    {
        return $this->getTransactionHandler()->recordDatastoreSegment($func, $parameters);
    }

    /**
     * @param string $name
     * @param string $license
     * @param bool   $xmit
     *
     * @return bool
     */
    public function setAppname($name, $license = '', $xmit = false)
    {
        return $this->getTransactionHandler()->setAppname($name, $license, $xmit);
    }

    /**
     * @param string $userValue
     * @param string $accountValue
     * @param string $productValue
     *
     * @return mixed
     */
    public function setUserAttributes($userValue, $accountValue, $productValue)
    {
        return $this->getTransactionHandler()->setUserAttributes($userValue, $accountValue, $productValue);
    }

    /**
     * @param string      $appname
     * @param string|null $license
     *
     * @return mixed
     */
    public function startTransaction($appname, $license = null)
    {
        return $this->getTransactionHandler()->startTransaction($appname, $license);
    }

    /**
     * @return TransactionHandlerInterface
     */
    public function getTransactionHandler()
    {
        return $this->transactionHandler;
    }

    /**
     * @param null $transactionHandler
     *
     * @return Handler $this
     */
    public function setTransactionHandler($transactionHandler)
    {
        $this->transactionHandler = $transactionHandler;

        return $this;
    }

    /**
     * @param array $eventAttributes
     */
    private function truncateAttributesThatAreToLongForTheCustomEvent(&$eventAttributes)
    {
        array_walk($eventAttributes, function (&$value) {
            if (is_string($value)) {
                if (strlen($value) > self::API_ATTRIBUTE_LENGTH_MAX) {
                    $value = substr($value, 0, self::API_ATTRIBUTE_LENGTH_MAX);
                }
            }
        });
    }

    /**
     * @param string $eventName
     * @param array  $eventAttributes
     *
     * @throws ToManyAttributesForCustomEventException
     */
    private function throwExceptionIfThereAreToManyAttributesForTheCustomEvent($eventName, $eventAttributes)
    {
        if (count($eventAttributes) > self::API_ATTRIBUTES_COUNT_MAX) {
            throw new ToManyAttributesForCustomEventException(sprintf(
                'There are more than %d attributes detected for the event %s. (%d)',
                self::API_ATTRIBUTES_COUNT_MAX,
                $eventName,
                count($eventAttributes)
            ));
        }
    }

    /**
     * @param array $eventAttributes
     */
    private function throwExceptionIfAnAttributeNameIsANumber($eventAttributes)
    {
        array_walk($eventAttributes, function (&$value, $attributeName) {
            if (is_numeric($attributeName)) {
                throw new NumericAttributeNameInEventException(sprintf(
                    'Encountered an numeric event attribute name "%s"',
                    $attributeName
                ));
            }
        });
    }

    /**
     * @return CustomEventHandlerInterface
     */
    private function getCustomEventHandler()
    {
        return $this->customEventHandler;
    }

    /**
     * @param CustomEventHandlerInterface $customEventHandler
     *
     * @return Handler $this
     */
    private function setCustomEventHandler(CustomEventHandlerInterface $customEventHandler)
    {
        $this->customEventHandler = $customEventHandler;

        return $this;
    }
}
