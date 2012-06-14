<?php

namespace DSpec;

use SplStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Compiler extends Scope 
{
    /**
     * Prefixed to avoid clashes with execution scope
     */
    protected $__stack;
    protected $__dispatcher;

    /**
     * @param ExampleGroup $eg
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ExampleGroup $eg, EventDispatcherInterface $dispatcher)
    {
        $this->__dispatcher = $dispatcher;
        $this->__stack = new SplStack();
        $this->__stack->push($eg);
    }

    /**
     * Describe something
     *
     * @param string $description - The thing we're describing
     * @param Closure $closure - How we're describing it
     */
    public function describe($description, \Closure $closure)
    {
        $closure = $closure->bindTo($this);
        $group = new ExampleGroup($description, $closure);
        $group->setParent($this->__stack->top());
        $this->__stack->top()->add($group);
        $this->__stack->push($group);
        $closure();
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
    public function it($example, \Closure $closure)
    {
        $closure = $closure->bindTo($this);
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
        $closure = $closure->bindTo($this);
        $this->__stack->top()->add(new Hook('beforeEach', $closure));
    }

    /**
     * After Each
     *
     * @param \Closure $closure
     */
    public function afterEach($closure)
    {
        $closure = $closure->bindTo($this);
        $this->__stack->top()->add(new Hook('afterEach', $closure));
    }

    /**
     * Compile
     *
     * @param array $files
     * @return ExampleGroup
     */
    public function compile(array $files)
    {
        foreach($files as $f)
        {
            include $f;
        }

        return $this->__stack->bottom();
    }


}
