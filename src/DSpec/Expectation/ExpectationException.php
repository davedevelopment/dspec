<?php

namespace DSpec\Expectation;

use Exception;

class ExpectationException extends Exception
{
    public function __construct($message, $actual, $expected)
    {
        parent::__construct($message);
        $this->actual = $actual;
        $this->expected = $expected;
    }
}


