<?php

namespace DSpec\Context;

use SplStack;
use DSpec\ExampleGroup;
use DSpec\Example;
use DSpec\Hook;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class SpecContext extends AbstractContext 
{
    /**
     * Prefixed to avoid clashes with execution scope
     */
    protected $__stack;

    /**
     * @param ExampleGroup $eg
     */
    public function __construct()
    {
        $this->__stack = new SplStack();
    }

    /**
     * Describe something
     *
     * @param string $description - The thing we're describing
     * @param Closure $closure - How we're describing it
     */
    public function describe($description, \Closure $closure)
    {
        $context = clone $this;
        $group = new ExampleGroup($description, $context, $this->__stack->top());
        $this->__stack->top()->add($group);
        $this->__stack->push($group);
        $context->run($closure);
        $this->__stack->pop();
        return $group;
    }

    /**
     * Proxy to Describe
     *
     * @param string $description
     * @param Closure $closure
     */
    public function context($description, \Closure $closure)
    {
        return $this->describe($description, $closure);
    }

    /**
     * It
     *
     * @param string $example
     * @param Closure $closure
     */
    public function it($example, \Closure $closure = null)
    {
        if ($closure === null) {
            $closure = function() { $this->pending(); };
        }
        $example = new Example($example, $closure);
        $example->setParent($this->__stack->top());
        $this->__stack->top()->add($example);
        return $example;
    } 

    /**
     * Before Each
     *
     * @param \Closure $closure
     */
    public function beforeEach($closure)
    {
        $this->__stack->top()->add(new Hook('beforeEach', $closure));
    }

    /**
     * After Each
     *
     * @param \Closure $closure
     */
    public function afterEach($closure)
    {
        $this->__stack->top()->add(new Hook('afterEach', $closure));
    }

    /**
     * @param array $files
     * @return ExampleGroup
     */
    public function load(array $files, ExampleGroup $eg = null)
    {
        $eg = $eg ?: new ExampleGroup("Suite", $this);

        $this->__stack->push($eg);

        foreach($files as $f)
        {
            include $f;
        }

        return $this->__stack->bottom();
    }
}
