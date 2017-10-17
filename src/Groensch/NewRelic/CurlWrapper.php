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

/**
 * Created by PhpStorm.
 * User: groensch
 * Date: 11.10.17
 * Time: 18:22
 */

namespace Groensch\NewRelic;

/**
 * Class CurlWrapper
 *
 * @codeCoverageIgnore
 */
class CurlWrapper
{
    private $curlHandle = null;
    private $curlOpened = false;

    /**
     * CurlWrapper constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param string $url
     */
    public function open($url)
    {
        if (!$this->curlOpened) {
            $this->curlHandle = curl_init($url);
        }

        $this->curlOpened = true;
    }

    /**
     *
     */
    public function close()
    {
        if ($this->curlOpened) {
            curl_close($this->curlHandle);
        }

        $this->curlOpened = false;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        return curl_exec($this->curlHandle);
    }

    /**
     * @param array $options
     *
     * @return CurlWrapper
     */
    public function setOptionsArray(array $options)
    {
        curl_setopt_array($this->curlHandle, $options);

        return $this;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return curl_getinfo($this->curlHandle);
    }

    /**
     * @return string
     */
    public function getError()
    {
        return curl_error($this->curlHandle);
    }

    /**
     * @return int
     */
    public function getErrno()
    {
        return curl_errno($this->curlHandle);
    }
}
