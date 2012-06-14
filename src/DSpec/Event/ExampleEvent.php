<?php

namespace DSpec\Event;

use Symfony\Component\EventDispatcher\Event;
use DSpec\Example;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ExampleEvent extends Event
{
    protected $example;

    public function __construct(Example $example)
    {
        $this->example = $example;
    }

    /**
     * Get example
     *
     * @return Example
     */
    public function getExample()
    {
        return $this->example;
    }
}
