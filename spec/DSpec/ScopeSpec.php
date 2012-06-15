<?php

require_once __DIR__ . '/../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php';

describe("Scope", function() {
    beforeEach(function() {
        $this->scope = new DSpec\Scope;
    });

    it("stores variables", function() {
        $this->scope->dave = 123;
        assertThat($this->scope->dave, equalTo(123));
    });

    describe("persist()", function() {
        it("stores variables", function() {
            $this->scope->persist('dave', 123);
            assertThat($this->scope->dave, equalTo(123));
        });
    });

    describe("tearDown()", function() {
        it("unsets variables", function() {
            $this->scope->dave = 123;
            $this->scope->bex = 456;
            $this->scope->tearDown();
            assertThat(isset($this->scope->dave), is(false));
            assertThat(isset($this->scope->bex), is(false));
        });

        it("preserves those that are persisted", function() {
            $this->scope->persist('dave', 123);
            $this->scope->bex = 456;
            $this->scope->tearDown();
            assertThat(isset($this->scope->bex), is(false));
            assertThat($this->scope->dave, equalTo(123));
        });
    });

});


