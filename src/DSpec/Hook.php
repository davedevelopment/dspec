<?php

namespace DSpec;

class Hook 
{
    protected $name;
    protected $closure;

    public function __construct($name, \Closure $closure)
    {
        $this->name = $name;
        $this->closure = $closure;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Reporter $reporter)
    {
        call_user_func($this->closure);
    }
}
