<?php

require_once __DIR__.'/../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php';

use Mockery as m;

describe("ExampleGroup", function() {

    beforeEach(function() {
        $this->context = m::mock(new DSpec\Context\AbstractContext);
        $this->eg = new DSpec\ExampleGroup("test", $this->context);
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

        it("runs hooks", function() {
            $obj = (object) ['count' => 0];
            $closure = function() use ($obj) { $obj->count++; };
            $this->eg->add(new DSpec\Hook("beforeEach", $closure));
            $this->eg->add(new DSpec\Example("one", $closure));
            $this->eg->add(new DSpec\Hook("afterEach", $closure));
            $this->eg->run($this->reporter);
            assertThat($obj->count, 3);
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

});
