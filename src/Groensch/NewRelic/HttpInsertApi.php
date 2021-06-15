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

namespace Groensch\NewRelic;

/**
 * Class HttpInsertApi
 */
class HttpInsertApi
{
    const API_TIMEOUT_SECONDS = 10;

    const RETRY_LIMIT = 5;

    private $apiKey = "";

    /** @var string */
    private $dataCollectorUrl = null;

    /** @var callable */
    private $errorHandler = null;

    /** @var CurlWrapper */
    private $curlHandler;

    /** @var bool */
    private $ignoreSSLVerification;

    /**
     * HttpApi constructor.
     * @param string      $dataCollectorUrl
     * @param string      $apiInsertKey
     * @param callable    $errorHandler          Parameters are: $errorMessage, $url, $payload
     * @param CurlWrapper $curlHandler
     * @param bool        $ignoreSSLVerification
     */
    public function __construct(string $dataCollectorUrl, string $apiInsertKey, callable $errorHandler = null, CurlWrapper $curlHandler = null, bool $ignoreSSLVerification = false)
    {
        $this->setDataCollectorUrl($dataCollectorUrl);
        $this->setInsertApiKey($apiInsertKey);
        $this->setErrorHandler(
            $errorHandler ? $errorHandler : function () {
            }
        );
        $this->setCurlHandler(
            $curlHandler ? $curlHandler : new CurlWrapper()
        );
        $this->ignoreSSLVerification = $ignoreSSLVerification;
    }

    /**
     * @param string $payload
     */
    public function sendCustomEvents(string $payload)
    {
        $retryCount = 0;
        $curl = $this->getCurlHandler();

        do {
            // Initalize curl
            $url = sprintf($this->dataCollectorUrl);
            $curl->open($url);
            $curlOptions = [
                CURLOPT_TIMEOUT => self::API_TIMEOUT_SECONDS,
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    sprintf("X-Insert-Key: %s", $this->getApiKey()),
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
            ];

            if ($this->isIgnoreSSLVerification()) {
                $curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
            }
            $curl->setOptionsArray($curlOptions);

            // Execute request to NewRelic api
            $curlRequestSuccessfull = $curl->execute();
            if (!$curlRequestSuccessfull) {
                $this->callErrorHandler(
                    sprintf(
                        "Curl request to NewRelic api was not successfull. Curl errno: %d, Curl error: '%s'",
                        $curl->getErrno(),
                        $curl->getError()
                    ),
                    $url,
                    $payload
                );

                return;
            }

            $info = $curl->getInfo();
            $statusCode = $info['http_code'];

            $curl->close();

            $isCustomEventSentSuccessfull = $statusCode === 200 ? true : false;
        } while ($this->shouldIRetry($statusCode) && ++$retryCount < self::RETRY_LIMIT);

        // If sending was not successfull, call an error handler
        if (!$isCustomEventSentSuccessfull) {
            $this->callErrorHandler(
                sprintf(
                    'Even after retrying multiple times it was not possible to send data to new relic api! Last status code: %d',
                    $statusCode
                ),
                $url,
                $payload
            );
        }
    }

    /**
     * @return CurlWrapper
     */
    public function getCurlHandler(): CurlWrapper
    {
        return $this->curlHandler;
    }

    /**
     * @param CurlWrapper $curlHandler
     *
     * @return HttpInsertApi $this
     */
    public function setCurlHandler(CurlWrapper $curlHandler): HttpInsertApi
    {
        $this->curlHandler = $curlHandler;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataCollectorUrl(): string
    {
        return $this->dataCollectorUrl;
    }

    /**
     * @param string $dataCollectorUrl
     *
     * @return HttpInsertApi
     */
    public function setDataCollectorUrl(string $dataCollectorUrl): self
    {
        $this->dataCollectorUrl = $dataCollectorUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIgnoreSSLVerification(): bool
    {
        return $this->ignoreSSLVerification;
    }

    /**
     * @param bool $ignoreSSLVerification
     *
     * @return HttpInsertApi
     */
    public function setIgnoreSSLVerification(bool $ignoreSSLVerification): self
    {
        $this->ignoreSSLVerification = $ignoreSSLVerification;

        return $this;
    }

    /**
     * @param string $errorMessage
     * @param string $url
     * @param string $payload
     */
    private function callErrorHandler(string $errorMessage, string $url, string $payload)
    {
        $this->getErrorHandler()($errorMessage, $url, $payload);
    }

    /**
     * @param int $statusCode
     *
     * @return bool
     */
    private function shouldIRetry(int $statusCode): bool
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
    private function setInsertApiKey($apiKey): HttpInsertApi
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return callable
     */
    private function getErrorHandler(): callable
    {
        return $this->errorHandler;
    }

    /**
     * @param callable $errorHandler
     *
     * @return HttpInsertApi $this
     */
    private function setErrorHandler(callable $errorHandler): HttpInsertApi
    {
        $this->errorHandler = $errorHandler;

        return $this;
    }
}
