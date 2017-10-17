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

use Groensch\NewRelic\CustomEventIsToBigException;
use Groensch\NewRelic\HttpInsertApi;

/**
 * Class Http
 */
class Http implements CustomEventHandlerInterface
{
    const API_EVENT_SIZE_PER_REQUEST_MAX = 1048576;

    /** @var HttpInsertApi */
    private $httpInsertApi;

    /**
     * Http constructor.
     *
     * @param HttpInsertApi $httpInsertApi
     */
    public function __construct(HttpInsertApi $httpInsertApi)
    {
        $this
            ->setHttpInsertApi($httpInsertApi)
        ;
    }

    /**
     * @param string $name
     * @param array  $attributes
     */
    public function recordCustomEvent($name, array $attributes)
    {
        $eventObject = [
            'eventType' => $name,
        ];

        $eventObject = array_merge($eventObject, $attributes);
        $payload = sprintf(
            '[%s]',
            json_encode($eventObject)
        );

        if (($eventSize = strlen($payload)) > self::API_EVENT_SIZE_PER_REQUEST_MAX) {
            throw new CustomEventIsToBigException(sprintf(
                'Custom event %s is to big. Limit is: %d bytes, Given: %d bytes',
                $name,
                self::API_EVENT_SIZE_PER_REQUEST_MAX,
                $eventSize
            ));
        }

        $this->getHttpInsertApi()->sendCustomEvents($payload);
    }

    /**
     * @return HttpInsertApi
     */
    public function getHttpInsertApi()
    {
        return $this->httpInsertApi;
    }

    /**
     * @param HttpInsertApi $httpInsertApi
     *
     * @return Http $this
     */
    public function setHttpInsertApi(HttpInsertApi $httpInsertApi)
    {
        $this->httpInsertApi = $httpInsertApi;

        return $this;
    }
}
