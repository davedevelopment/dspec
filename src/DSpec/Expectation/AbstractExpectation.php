<?php

namespace DSpec\Expectation;

abstract class AbstractExpectation
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

    abstract public function toEqual($value);
    abstract public function notToEqual($value);
}
