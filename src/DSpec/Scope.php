<?php

namespace DSpec;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Scope
{
    protected $data = array();

    protected $persistant = array();

    public function tearDown()
    {
        $persistant = array();
        foreach ($this->data as $key => $value) {
            if (!empty($this->persistant[$key])) {
                $persistant[$key] = $value;
            }
        }
        $this->data = $persistant;
    }

    public function persist($key, $value)
    {
        $this->data[$key] = $value;
        $this->persistant[$key] = true;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
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
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }
}
