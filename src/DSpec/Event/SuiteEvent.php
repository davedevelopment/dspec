<?php

namespace DSpec\Event;

use Symfony\Component\EventDispatcher\Event;
use DSpec\ExampleGroup;

class SuiteEvent extends Event
{
    /**
     * @var ExampleGroup
     */
    protected $exampleGroup;

    /**
     * Constructor
     *
     * @param ExampleGroup $exampleGroup
     */
    public function __construct(ExampleGroup $exampleGroup)
    {
        $this->exampleGroup = $exampleGroup;
    }

    /**
     * Get Example GRoup
     *
     * @return ExampleGroup
     */
    public function getExampleGroup()
    {
        return $this->exampleGroup;
    }
}
