<?php

namespace DSpec\Expectation;

use DSpec\Expectation\AbstractExpectation;
use DSpec\Expectation\ExpectationException;
use DSpec\Expectation\Assertion;
use Exception;

/**
 * These matchers need pulling out in to something standalone, have used 
 * Assert\Assertion in here as a temp fix, Hamcrest maybe more appropriate, but 
 * may have to roll my own
 */
class Subject
{
    protected $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function toEqual($value)
    {
        try {
            Assertion::eq($this->getSubject(), $value);
            return true;
        } catch (AssertionException $ae) {
            throw new ExpectationException("Expected %s to equal %s", $this->getSubject(), $value);
        }
    }

    public function notToEqual($value)
    {
        try {
            $this->toEqual($value);
        } catch (ExpectationException $e) {
            return true;
        }

        throw new ExpectationException("Expected %s to not equal %s", $this->getSubject(), $value);
    }

    public function toBeTrue()
    {
        try {
            Assertion::true($this->getSubject());
            return true;
        } catch (AssertionException $ae) {
            throw new ExpectationException("Expected %s to be true", $this->getSubject(), true);
        }
    }

    public function toBeFalse()
    {
        try {
            Assertion::false($this->getSubject());
            return true;
        } catch (AssertionException $ae) {
            throw new ExpectationException("Expected %s to be false", $this->getSubject(), false);
        }
    }

    public function toThrow($exception, $message = null)
    {
        if (!is_string($exception)) {
            $exception = get_class($exception);
        }

        try {
            if (is_callable($this->getSubject())) {
                call_user_func($this->getSubject());
            } else {
                eval($this->getSubject());
            }
        } catch (Exception $e) {
            if ($e instanceof $exception) {
                if ($message == null) {
                    return true;
                }

                try {
                    Assertion::eq($e->getMessage(), $message);
                    return true;
                } catch (AssertionException $ae) {
                    throw new ExpectationException("Expected exception message to be %s", $e->getMessage(), $message);
                }
            }
            throw new ExpectationException("Expected a %s to be thrown, got %s", $e, $exception);
        }

        throw new ExpectationException("Expected a %s to be thrown, but no exception thrown", null, $exception);
    }
}
