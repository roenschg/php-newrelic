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

/**
 * This handler is intended for local usage or in environments where NewRelic is not available.
 */
class CrossedFingers implements CustomEventHandlerInterface
{
    /**
     * @var CustomEventHandlerInterface
     */
    private $customEventHandler;

    /**
     * CrossedFingers constructor.
     * @param CustomEventHandlerInterface|null $customEventHandler
     */
    public function __construct(?CustomEventHandlerInterface $customEventHandler = null)
    {
        if (null === $customEventHandler) {
            $customEventHandler = new PHPAgent();
        }

        $this->customEventHandler = $customEventHandler;
    }

    /**
     * @param string $name
     * @param array  $attributes
     */
    public function recordCustomEvent(string $name, array $attributes)
    {
        if (extension_loaded('newrelic')) {
            $this->customEventHandler->recordCustomEvent($name, $attributes);
        }
    }
}
