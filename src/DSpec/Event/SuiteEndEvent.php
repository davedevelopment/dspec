<?php

namespace DSpec\Event;

use DSpec\ExampleGroup;
use DSpec\Reporter;

class SuiteEndEvent extends SuiteEvent
{
    /**
     * @var Reporter
     */
    protected $reporter;

    /**
     * Constructor
     *
     * @param ExampleGroup $exampleGroup
     * @param Reporter $reporter
     */
    public function __construct(ExampleGroup $exampleGroup, Reporter $reporter)
    {
        parent::__construct($exampleGroup);
        $this->reporter = $reporter;
    }

    /**
     * Get reporter
     *
     * @return Reporter
     */
    public function getReporter()
    {
        return $this->reporter;
    }
}
