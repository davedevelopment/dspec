<?php 

namespace DSpec;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use DSpec\Event\ExampleFailEvent;
use DSpec\Event\ExamplePassEvent;
use DSpec\Event\ExamplePendEvent;
use DSpec\Event\ExampleSkipEvent;
use DSpec\Events;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Reporter
{
    protected $failures = array();
    protected $passes = array();
    protected $pending = array();
    protected $skipped = array();
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * An example failed
     *
     * @param Example $example
     */
    public function exampleFailed(Example $example)
    {
        $this->failures[] = $example;
        $event = new ExampleFailEvent($example);
        $this->dispatcher->dispatch(Events::EXAMPLE_FAIL, $event);
    }

    /**
     * An example passed
     *
     * @param Example $example
     */
    public function examplePassed(Example $example)
    {
        $this->passes[] = $example;
        $event = new ExamplePassEvent($example);
        $this->dispatcher->dispatch(Events::EXAMPLE_PASS, $event);
    }

    /**
     * An example is pending
     *
     * @param Example $example
     */
    public function examplePending(Example $example)
    {
        $this->pending[] = $example;
        $event = new ExamplePendEvent($example);
        $this->dispatcher->dispatch(Events::EXAMPLE_PEND, $event);
    }

    /**
     * An example is skipped
     *
     * @param Example $example
     */
    public function exampleSkipped(Example $example)
    {
        $this->skipped[] = $example;
        $event = new ExampleSkipEvent($example);
        $this->dispatcher->dispatch(Events::EXAMPLE_SKIP, $event);
    }

    /**
     * Get passing examples
     *
     * @return array
     */
    public function getPasses()
    {
        return $this->passes;
    }

    /**
     * Get failing examples
     *
     * @return array
     */
    public function getFailures()
    {
        return $this->failures;
    }

    /**
     * Get pending
     *
     * @return array
     */
    public function getPending()
    {
        return $this->pending;
    }

    /**
     * Get skipped
     *
     * @return array
     */
    public function getSkipped()
    {
        return $this->skipped;
    }
}
