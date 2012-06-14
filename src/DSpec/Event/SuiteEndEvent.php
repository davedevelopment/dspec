<?php

namespace DSpec\Event;

use DSpec\ExampleGroup;
use DSpec\Reporter;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
