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

/**
 * Class Handler
 */
class Handler
{
    const API_ATTRIBUTES_COUNT_MAX = 255;
    const API_ATTRIBUTE_LENGTH_MAX = 4096;

    private $customEventHandler = null;

    /**
     * Handler constructor.
     * @param CustomEventHandlerInterface $customEventHandler
     */
    public function __construct(CustomEventHandlerInterface $customEventHandler)
    {
        $this
            ->setCustomEventHandler($customEventHandler)
        ;
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
