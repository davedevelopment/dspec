<?php

namespace DSpec;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
