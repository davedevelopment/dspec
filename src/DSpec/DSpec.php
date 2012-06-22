<?php

namespace DSpec;

use DSpec\Context\SpecContext;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Static class that simply acts as a front controller, passing things where 
 * they need to go
 */
class DSpec
{
    protected static $context;

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(static::getContext(), $name), $arguments);
    }

    /**
     * @param SpecContext $context;
     */
    public static function getContext()
    {
        if (static::$context) {
            return static::$context;
        }

        return static::$context = new SpecContext();
    }

    /**
     * @param SpecContext $context;
     */
    public static function setContext(SpecContext $context)
    {
        static::$context = $context;
    }
}
