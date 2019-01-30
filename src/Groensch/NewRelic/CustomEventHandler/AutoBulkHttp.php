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

declare(strict_types = 1);

namespace Groensch\NewRelic\CustomEventHandler;

use Groensch\NewRelic\CustomEventIsToBigException;
use Groensch\NewRelic\HttpInsertApi;
use Groensch\NewRelic\InvalidArgumentException;

/**
 * Class CustomEventBuffer
 */
class AutoBulkHttp implements CustomEventHandlerInterface
{
    private $customEventBuffer = "";
    private $customEventBufferCount = 0;

    /**
     * @var int
     */
    protected $timeToPassInSec = 30;
    /**
     * @var int
     */
    protected $lastTimeBufferWasFlushed;

    const API_EVENT_COUNT_PER_REQUEST_MAX = 1000;
    const API_EVENT_SIZE_PER_REQUEST_MAX = 1048576;

    /** @var HttpInsertApi */
    private $newRelicHttpApi = null;

    /**
     * AutoBulkHttp constructor.
     * @param HttpInsertApi $httpApi
     * @param int           $timeToPassInSec
     *
     * @throws \Exception
     */
    public function __construct(HttpInsertApi $httpApi, int $timeToPassInSec = 30)
    {
        $this
            ->setNewRelicHttpApi($httpApi)
            ->startTimer()
        ;

        $this->setTimeToPassInSec($timeToPassInSec);
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->flushCustomEventBuffer();
    }

    /**
     * @return int
     */
    public function getTimeToPassInSec(): int
    {
        return $this->timeToPassInSec;
    }

    /**
     * @param int $timeToPassInSec
     */
    public function setTimeToPassInSec(int $timeToPassInSec)
    {
        $this->timeToPassInSec = $timeToPassInSec;
    }

    /**
     * @return int
     */
    public function getLastTimeBufferWasFlushed(): int
    {
        return $this->lastTimeBufferWasFlushed;
    }

    /**
     * @param string $name
     * @param array  $attributes
     *
     * @throws CustomEventIsToBigException
     */
    public function recordCustomEvent(string $name, array $attributes)
    {
        // If the buffer get´s to full before adding a new custom event to it, we flush it and send the data
        // to new relic
        if (!$this->isEnoughSpaceToAddCustomEventToBuffer($name, $attributes) or
            $this->isTimeOver()
        ) {
            $this->flushCustomEventBuffer();
            $this->startTimer();
        }

        // We add the custom event to the buffer
        $this->addCustomEventToBuffer($name, $attributes);
    }

    /**
     *
     */
    public function startTimer()
    {
        $this->lastTimeBufferWasFlushed = time();
    }
    /**
     * @return bool
     */
    public function isTimeOver()
    {
        $currentTime = time();
        if (($currentTime - $this->lastTimeBufferWasFlushed) <  $this->timeToPassInSec) {
            return false;
        }

        return true;
    }


    /**
     *
     */
    private function flushCustomEventBuffer()
    {
        if (strlen($this->customEventBuffer) <= 0) {
            return;
        }

        $payload = sprintf(
            '[%s]',
            $this->customEventBuffer
        );

        $this->getNewRelicHttpApi()->sendCustomEvents($payload);

        $this->customEventBuffer = "";
        $this->customEventBufferCount = 0;
    }

    /**
     * @param string $eventName
     * @param array  $eventData
     */
    private function addCustomEventToBuffer(string $eventName, array $eventData)
    {
        $eventJson = $this->convertCustomEventInfoToJson($eventName, $eventData);

        if ($this->customEventBufferCount < 1) { // If it is the first event we just add it to the buffer
            $this->customEventBuffer .= $eventJson;
        } else { // Otherwise we need to add a "," at the beginning
            $this->customEventBuffer .= sprintf(",%s", $eventJson);
        }

        $this->customEventBufferCount++;
    }

    /**
     * @param string $eventName
     * @param array  $eventData
     *
     * @return int
     */
    private function getEventSizeInBytes(string $eventName, array $eventData): int
    {
        $size = strlen($this->convertCustomEventInfoToJson($eventName, $eventData));

        if ($this->customEventBufferCount === 0) {
            $size += 1;
        }

        return $size;
    }

    /**
     * @param string $eventName
     * @param array  $eventData
     *
     * @return string
     */
    private function convertCustomEventInfoToJson(string $eventName, array $eventData): string
    {
        $customEvent = [
            'eventType' => $eventName,
            'timestamp' => time(),
        ];
        $customEvent = array_merge($customEvent, $eventData);

        return json_encode($customEvent);
    }

    /**
     * @return int
     */
    private function getInternalCustomEventBufferCount(): int
    {
        return $this->customEventBufferCount;
    }

    /**
     * @return int
     */
    private function getInternalCustomEventBufferSizeInBytes(): int
    {
        // We will have two brackets around the buffer later
        return strlen($this->customEventBuffer) + 2;
    }

    /**
     * @param string $eventName
     * @param array  $eventData
     *
     * @return bool
     *
     * @throws CustomEventIsToBigException
     */
    private function isEnoughSpaceToAddCustomEventToBuffer(string $eventName, array $eventData): bool
    {
        $possibleEventsLeft = self::API_EVENT_COUNT_PER_REQUEST_MAX - $this->getInternalCustomEventBufferCount();
        if ($possibleEventsLeft < 1) {
            return false;
        }

        $eventSize = $this->getEventSizeInBytes($eventName, $eventData);

        // Check if the custom event is smaller than the api limit
        if ($eventSize > self::API_EVENT_SIZE_PER_REQUEST_MAX) {
            throw new CustomEventIsToBigException(sprintf(
                'Custom event %s is to big. Limit is: %d bytes, Given: %d bytes',
                $eventName,
                self::API_EVENT_SIZE_PER_REQUEST_MAX,
                $eventSize
            ));
        }

        $eventSizeLeftInBuffer = self::API_EVENT_SIZE_PER_REQUEST_MAX - $this->getInternalCustomEventBufferSizeInBytes();
        if ($eventSizeLeftInBuffer < $eventSize) {
            return false;
        }

        return true;
    }

    /**
     * @return HttpInsertApi
     */
    private function getNewRelicHttpApi(): HttpInsertApi
    {
        return $this->newRelicHttpApi;
    }

    /**
     * @param HttpInsertApi $newRelicHttpApi
     *
     * @return AutoBulkHttp $this
     */
    private function setNewRelicHttpApi(HttpInsertApi $newRelicHttpApi): AutoBulkHttp
    {
        $this->newRelicHttpApi = $newRelicHttpApi;

        return $this;
    }
}
