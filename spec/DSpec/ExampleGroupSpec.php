<?php

require_once __DIR__.'/../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php';

use Mockery as m;

describe("ExampleGroup", function() {

    beforeEach(function() {
        $this->context = m::mock(new DSpec\Context\AbstractContext);
        $this->eg = new DSpec\ExampleGroup("test", $this->context);
    });

    describe("total()", function() {
        it("returns the total number of examples in this group and it's descendants", function() {
            $closure = function() {};
            $innerEg = new DSpec\ExampleGroup("Inner", $this->context, $this->eg);
            $innerEg->add(new DSpec\Example("one", $closure));
            $this->eg->add($innerEg);
            $this->eg->add(new DSpec\Hook("beforeEach", $closure));
            $this->eg->add(new DSpec\Example("one", $closure));
            assertThat($this->eg->total(), equalTo(2));
        });
    });

    describe("getDescendants()", function() {
        it("returns the descendants and itself", function() {
            $closure = function() {};
            $innerEg = new DSpec\ExampleGroup("Inner", $this->context, $this->eg);
            $innerEg->add(new DSpec\Example("one", $closure));
            $this->eg->add($innerEg);
            $this->eg->add(new DSpec\Hook("beforeEach", $closure));
            $this->eg->add(new DSpec\Example("one", $closure));
            assertThat(count($this->eg->getDescendants()), equalTo(4));
        });
    });

    describe("run()", function() {

        beforeEach(function() {
            $this->reporter = m::mock("DSpec\Reporter");
            $this->reporter->shouldIgnoreMissing();
        });

        afterEach(function() {
            $this->reporter->mockery_verify();
        });

        it("runs any children ExampleGroups", function() {
            $child = m::mock("\DSpec\ExampleGroup")
                ->shouldReceive("run")
                ->once()
                ->mock();

            $this->eg->add($child);
            $this->eg->run($this->reporter);
            $child->mockery_verify();
        });


        context("when hooks are present", function() {

            beforeEach(function() {
                $this->obj = $obj = (object)['string' => ''];
                $this->factory = function($letter) use ($obj) {
                    return function() use ($letter, $obj) { $obj->string.= $letter;};
                };
            });

            it("runs hooks in the order added", function() {
                $this->eg->add(new DSpec\Hook("beforeEach", $this->factory->__invoke("A")));
                $this->eg->add(new DSpec\Example("one", $this->factory->__invoke("B")));
                $this->eg->add(new DSpec\Hook("afterEach", $this->factory->__invoke("C")));
                $this->eg->run($this->reporter);
                assertThat($this->obj->string, equalTo("ABC"));
            });

            it("runs them in the in the correct order", function() {
                $innerEg = new DSpec\ExampleGroup("Inner", $this->context, $this->eg);
                $innerEg->add(new DSpec\Hook("beforeEach", $this->factory->__invoke("A")));
                $innerEg->add(new DSpec\Example("one", $this->factory->__invoke("B")));
                $innerEg->add(new DSpec\Hook("afterEach", $this->factory->__invoke("C")));
                $this->eg->add($innerEg);
                $this->eg->add(new DSpec\Hook("beforeEach", $this->factory->__invoke("D")));
                $this->eg->add(new DSpec\Hook("afterEach", $this->factory->__invoke("E")));
                $this->eg->add(new DSpec\Example("one", $this->factory->__invoke("F")));
                $this->eg->run($this->reporter);
                assertThat($this->obj->string, equalTo("DABCEDFE"));
            });

        });

        it("run any examples", function() {
            $obj = (object) ['count' => 0];
            $closure = function() use ($obj) { $obj++; };
            $this->eg->add(new DSpec\Example("one", $closure));
            $this->eg->add(new DSpec\Example("two", $closure));
            $this->eg->run($this->reporter);
            assertThat($obj->count, 2);
        });

        describe("results", function() {
            it("captures and reports skipped examples", function() {
                $this->eg->add($ex = new DSpec\Example("skip", function() { throw new DSpec\Exception\SkippedExampleException(); }));
                $this->reporter->shouldReceive("exampleSkipped")->with($ex)->once();
                $this->eg->run($this->reporter);
            });

            it("captures and reports failures", function() {
                $this->eg->add($ex = new DSpec\Example("fail", function() { throw new Exception(); }));
                $this->reporter->shouldReceive("exampleFailed")->with($ex)->once();
                $this->eg->run($this->reporter);
            });

            it("reports passes", function() {
                $this->eg->add($ex = new DSpec\Example("pass", function() { }));
                $this->reporter->shouldReceive("examplePassed")->with($ex)->once();
                $this->eg->run($this->reporter);
            });

            it("captures and reports pending", function() {
                $this->eg->add($ex = new DSpec\Example("pending", function() { throw new DSpec\Exception\PendingExampleException(); }));
                $this->reporter->shouldReceive("examplePending")->with($ex)->once();
                $this->eg->run($this->reporter);
            });

            it("captures and reports failures in before hook", function() {
                $this->eg->add(new DSpec\Hook("beforeEach", function() { throw new Exception(); }));
                $this->eg->add($ex = new DSpec\Example("pass", function() { }));
                $this->reporter->shouldReceive("exampleFailed")->with($ex)->once();
                $this->eg->run($this->reporter);
            });
            it("captures and reports failures in after hook", function() {
                $this->eg->add(new DSpec\Hook("afterEach", function() { throw new Exception(); }));
                $this->eg->add($ex = new DSpec\Example("pass", function() { }));
                $this->reporter->shouldReceive("exampleFailed")->with($ex)->once();
                $this->eg->run($this->reporter);
            });
        });
    });


    describe("hasFailures()", function() {

        it("returns true if any examples failed", function() {
            $example = new DSpec\Example("test", function() {});
            $example->failed(new Exception());
            $this->eg->add($example);
            assertThat($this->eg->hasFailures(), equalTo(true));
        });

        it("returns false if all examples passed", function() {
            $example = new DSpec\Example("test", function() {});
            $example->passed();
            $this->eg->add($example);
            assertThat($this->eg->hasFailures(), equalTo(false));
        });

        it("returns true if child example groups have failed examples", function() {
            $example = new DSpec\Example("test", function() {});
            $example->passed();
            $this->eg->add($example);
            $example = new DSpec\Example("test", function() {});
            $example->failed(new Exception());
            $innerEg = new DSpec\ExampleGroup("test", $this->context, $this->eg);
            $innerEg->add($example);
            $this->eg->add($innerEg);
            assertThat($this->eg->hasFailures(), equalTo(true));
        });

        it("returns false if child example groups dont have failed examples", function() {
            $example = new DSpec\Example("test", function() {});
            $example->passed();
            $innerEg = new DSpec\ExampleGroup("test", $this->context, $this->eg);
            $innerEg->add($example);
            $this->eg->add($innerEg);
            $this->eg->add($example);
            assertThat($this->eg->hasFailures(), equalTo(false));
        });
    });
});
