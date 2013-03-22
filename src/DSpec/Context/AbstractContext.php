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
    protected $__factories = array();

    public function run(\Closure $closure)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $closure = $closure->bindTo($this);
        }

        $rfc = new \ReflectionFunction($closure);
        $args = array(); 
            
        foreach ($rfc->getParameters() as $param) {
            if (!isset($this->{$param->getName()})) {
                throw new \InvalidArgumentException("This context does not have a variable named {$param->getName()}");
            }

            $args[] = $this->{$param->getName()};
        } 

        return call_user_func_array($closure, $args);
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

    public function setFactory($name, \Closure $closure)
    {
        $this->__factories[$name] = $closure;      
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

        if (array_key_exists($name, $this->__factories)) {
            $value = $this->run($this->__factories[$name]);
            $this->__data[$name] = $value;
            return $value;
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
        return isset($this->__data[$name]) || isset($this->__factories[$name]);
    }

    public function __unset($name)
    {
        unset($this->__data[$name]);
    }
}
