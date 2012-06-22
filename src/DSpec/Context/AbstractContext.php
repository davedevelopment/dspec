<?php

namespace DSpec\Context;

use DSpec\Exception\SkippedExampleException;
use DSpec\Exception\FailedExampleException;
use DSpec\Exception\PendingExampleException;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class AbstractContext
{
    protected $__data = array();

    public function run(\Closure $closure)
    {
        $closure = $closure->bindTo($this);
        $closure();
    }

    /**
     * @param string $message
     */
    public function pending($message = "{no message}")
    {
        throw new PendingExampleException($message);
    }

    /**
     * @param string $message
     */
    public function skip($message = "{no message}")
    {
        throw new SkippedExampleException($message);
    }

    /**
     * @param string $message
     */
    public function fail($message = "{no message}")
    {
        throw new FailedExampleException($message);
    }

    public function __set($name, $value)
    {
        $this->__data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->__data)) {
            return $this->__data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function __isset($name)
    {
        return isset($this->__data[$name]);
    }

    public function __unset($name)
    {
        unset($this->__data[$name]);
    }
}
