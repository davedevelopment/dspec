<?php

/**
 * Need a bootstrap for this
 */
require_once __DIR__ . '/../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php';

use Mockery as m;

describe("Compiler", function() {

    beforeEach(function() {
        $this->eg = m::mock("DSpec\ExampleGroup");
        $this->eg->shouldIgnoreMissing();
        $this->dispatcher = m::mock("Symfony\Component\EventDispatcher\EventDispatcherInterface");
        $this->dispatcher->shouldIgnoreMissing();
        $this->compiler = new DSpec\Compiler($this->eg, $this->dispatcher);
    });

    afterEach(function() {
        $this->eg->mockery_verify();
        $this->dispatcher->mockery_verify();
        $this->tearDown();
    });

    describe("describe", function() {

        it("adds an ExampleGroup to the root ExampleGroup", function() {
            $this->eg->shouldReceive("add")->with(m::type("DSpec\ExampleGroup"))->once();
            $this->compiler->describe("test", function() {});
        });

        it("binds itself to the closure", function() {
            $eg = $this->compiler->describe("test", function() { return $this; });
            $closure = $eg->getClosure();
            assertThat($closure(), equalTo($this->compiler));
        });

        it("executes the closure", function() {
            $dave123 = new stdClass;
            $this->compiler->describe("test1", function() use ($dave123) {
                $dave123->dave = 'changed';
            });
            assertThat($dave123->dave, equalTo("changed"));
        });

        context("when nesting describes", function() {

            it("adds an ExampleGroup to the previous ExampleGroup", function() {
                // only once, twice would suggest it's not added to the child
                $this->eg->shouldReceive("add")->with(m::type("DSpec\ExampleGroup"))->andReturnUsing(function($eg) {
                    $this->innerEg = $eg;
                })->once();

                $this->compiler->describe("test", function() {
                    $this->describe("inner", function() {
                    });
                });

                assertThat(count($this->innerEg->getDescendants()), equalTo(2));
                unset($this->innerEg); // tidy up
            });

        });

    });

    describe("it", function() {

        it("binds itself to the closure", function() {
            $example = $this->compiler->it("test", function() { return $this; });
            $closure = $example->getClosure();
            assertThat($closure(), equalTo($this->compiler));
        });

        it("adds an Example to the current ExampleGroup", function() {
            $this->eg->shouldReceive("add")->with(m::type("DSpec\ExampleGroup"))->andReturnUsing(function($eg) {
                $this->innerEg = $eg;
            })->once();

            $this->compiler->describe("test", function() {
                $this->it("inner", function() {
                });
            });

            assertThat(count($this->innerEg->getDescendants()), equalTo(2));
            unset($this->innerEg); // tidy up
        });

    });
});
