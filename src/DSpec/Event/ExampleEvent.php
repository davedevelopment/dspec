<?php

namespace DSpec\Event;

use Symfony\Component\EventDispatcher\Event;
use DSpec\Example;

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
