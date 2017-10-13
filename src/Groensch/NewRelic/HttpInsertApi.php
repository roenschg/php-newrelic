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
 * Class HttpInsertApi
 */
class HttpInsertApi
{
    const API_TIMEOUT_SECONDS = 10;

    const RETRY_LIMIT = 5;

    private $apiKey = "";
    private $apiAccountId = null;

    /** @var callable */
    private $errorHandler = null;

    /** @var CurlWrapper */
    private $curlHandler;

    /**
     * HttpApi constructor.
     * @param int      $apiAccountId
     * @param string   $apiInsertKey
     * @param callable $errorHandler
     */
    public function __construct($apiAccountId, $apiInsertKey, callable $errorHandler = null)
    {
        $this
            ->setApiAccountId($apiAccountId)
            ->setInsertApiKey($apiInsertKey)
            ->setErrorHandler(
                $errorHandler ? $errorHandler : function () {
                }
            )
        ;

        $this->curlHandler = new CurlWrapper();
    }

    /**
     * @param string $payload
     */
    public function sendCustomEvents($payload)
    {
        $retryCount = 0;
        $curl = $this->getCurlHandler();

        do {
            // Initalize curl
            $url = sprintf('https://insights-collector.newrelic.com/v1/accounts/%s/events', $this->getApiAccountId());
            $curl->open($url);
            $curl->setOptionsArray([
                CURLOPT_TIMEOUT => self::API_TIMEOUT_SECONDS,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    sprintf("X-Insert-Key: %s", $this->getApiKey()),
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
            ]);

            // Execute request to NewRelic api
            $curlRequestSuccessfull = $curl->execute();
            if (false === $curlRequestSuccessfull) {
                throw new \Exception(
                    sprintf(
                        "Curl request to NewRelic api was not successfull. Data: %s",
                        json_encode([
                            'url' => $url,
                            'curl_error' => $curl->getError(),
                        ])
                    ),
                    $curl->getErrno()
                );
            }

            $info = $curl->getInfo();
            $statusCode = $info['http_code'];

            $curl->close();

            $isCustomEventSentSuccessfull = $statusCode === 200 ? true : false;
        } while ($this->shouldIRetry($statusCode) && ++$retryCount < self::RETRY_LIMIT);

        // If sending was not successfull, call an error handler
        if (!$isCustomEventSentSuccessfull) {
            call_user_func(
                $this->getErrorHandler(),
                $payload,
                [
                    'url' => $url,
                    'lastStatusCode' => $statusCode,
                ]
            );
        }
    }

    /**
     * @return CurlWrapper
     */
    public function getCurlHandler()
    {
        return $this->curlHandler;
    }

    /**
     * @param CurlWrapper $curlHandler
     *
     * @return HttpInsertApi $this
     */
    public function setCurlHandler(CurlWrapper $curlHandler)
    {
        $this->curlHandler = $curlHandler;

        return $this;
    }

    /**
     * @param int $statusCode
     *
     * @return bool
     */
    private function shouldIRetry($statusCode)
    {
        if ($statusCode >= 400 && $statusCode < 500) { // NO! You fucked up
            return false;
        }

        if ($statusCode >= 500 && $statusCode < 600) { // Yes but only x times
            return true;
        }

        if ($statusCode >= 300 && $statusCode < 400) {
            return false;
        }

        if ($statusCode >= 200 && $statusCode < 300) {
            return false;
        }

        return false; // unkown status code
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param string $apiKey
     *
     * @return HttpInsertApi $this
     */
    private function setInsertApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return null
     */
    private function getApiAccountId()
    {
        return $this->apiAccountId;
    }

    /**
     * @param null $apiAccountId
     *
     * @return HttpInsertApi $this
     */
    private function setApiAccountId($apiAccountId)
    {
        $this->apiAccountId = $apiAccountId;

        return $this;
    }

    /**
     * @return callable
     */
    private function getErrorHandler()
    {
        return $this->errorHandler;
    }

    /**
     * @param callable $errorHandler
     *
     * @return HttpInsertApi $this
     */
    private function setErrorHandler(callable $errorHandler)
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }
}
