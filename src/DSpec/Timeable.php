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

trait Timeable
{
    protected $startTime;
    protected $endTime;

    public function startTimer()
    {
        $this->startTime = microtime();
    }

    public function endTimer()
    {
        $this->endTime = microtime();
    }

    public function getTime()
    {
        $start = array_sum(explode(" ", $this->startTime));
        $end   = array_sum(explode(" ", $this->endTime));

        return $end - $start;
    }
}
