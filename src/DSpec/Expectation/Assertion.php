<?php

namespace DSpec\Expectation;

use Assert\Assertion as BaseAssertion;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = 'DSpec\Expectation\AssertionException';
}
