<?php

use DSpec\Expectation\Subject;
use DSpec\Expectation\ExpectationException;

describe("DSpec\Expectation\Subject", function() {

    it("checks for equality", function() {
        $e = new Subject("dave");
        expect($e->toEqual("dave"))->toBeTrue();
        expect(function() use ($e) {
            $e->toEqual("dave2");
        })->toThrow("DSpec\Expectation\ExpectationException");
    });

    it("checks for non equality", function() {
        $e = new Subject("dave");
        expect($e->notToEqual("dave2"))->toBeTrue();

        expect(function() use ($e) {
            $e->notToEqual("dave");
        })->toThrow("DSpec\Expectation\ExpectationException");
    });

    it("checks for truthiness", function() {
        $e = new Subject(true);
        expect($e->toBeTrue())->toBeTrue();

        $e = new Subject(false);
        expect(function() use ($e) {
            $e->toBeTrue();
        })->toThrow("DSpec\Expectation\ExpectationException");
    });

    it("checks for falsiness", function() {
        $e = new Subject(false);
        expect($e->toBeFalse())->toBeTrue();

        $e = new Subject(true);
        expect(function() use ($e) {
            $e->toBeFalse();
        })->toThrow("DSpec\Expectation\ExpectationException");
    });

    context("checking for exceptions", function() {
        it("checks an exception is thrown", function() {
            $e = new Subject(function() {});
            expect(function() use ($e) {
                $e->toThrow("Exception");
            })->toThrow("DSpec\Expectation\ExpectationException");
        });

        it("checks for the type of exception", function() {
            $e = new Subject(function() {throw new \RunTimeException;});
            expect(function() use ($e) {
                $e->toThrow("InvalidArgumentException");
            })->toThrow("DSpec\Expectation\ExpectationException");
        });

        it("checks the exception message", function() {
            $e = new Subject(function() {throw new \Exception("dave123");});
            expect(function() use ($e) {
                $e->toThrow("Exception", "dave1456");
            })->toThrow("DSpec\Expectation\ExpectationException");
        });
    });

});
