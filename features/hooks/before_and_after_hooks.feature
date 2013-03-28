Feature: Before and After hooks

    Use `before` and `after` hooks to execute arbitrary code before and/or
    after the body of an example is run:

    @php5.4
    Scenario: define beforeEach block
        Given a file named "BeforeEachSpec.php" with:
            """php
            <?php

            class Thing {
                public $widgets = array();
            }
            
            describe("Thing", function() {
                beforeEach(function() {
                    $this->thing = new Thing();
                });

                describe("initialised in beforeEach", function() {

                    it("has 0 widgets", function() {
                        if (!empty($this->thing->widgets)) {
                            throw new \Exception;
                        }
                    });

                    it("can get accept new widgets", function() {
                        $this->thing->widgets[] = new stdClass;
                    });

                    it("does not share state across examples", function() {
                        if (!empty($this->thing->widgets)) {
                            throw new \Exception;
                        }
                    });
                });
            });
            """
        When I run `dspec BeforeEachSpec.php`
        Then the output should contain "3 examples passed"

    Scenario: Hooks are run in the order they are defined
        Given a file named "BeforeAfterOrderSpec.php" with:
            """php
            <?php
            describe("before and after callbacks", function() {
                beforeEach(function() {
                    echo "before one\n";
                });

                beforeEach(function() {
                    echo "before two\n";
                });

                afterEach(function() {
                    echo "after one\n";
                });

                afterEach(function() {
                    echo "after two\n";
                });

                it("gets run in order", function() {
                    echo "it\n";
                });
            });
        """
        When I run `dspec BeforeAfterOrderSpec.php`
        Then the output should contain:
        """
        before one
        before two
        it
        after two
        after one
        """

    Scenario: An Exception in a beforeEach is captured and reported as failure
        Given a file named "ExceptionInBeforeEachSpec.php" with:
            """php
            <?php 
            describe("exception in beforeEach", function() {
                beforeEach(function() {
                    throw new \Exception();
                });

                it("is reported as failure", function() {

                });
            });
            """
        When I run `dspec ExceptionInBeforeEachSpec.php`
        Then the output should contain "1 of 1 examples failed"


    Scenario: An Exception in a afterEach is captured and reported as failure
        Given a file named "ExceptionInAfterEachSpec.php" with:
            """php
            <?php 
            describe("exception in afterEach", function() {
                afterEach(function() {
                    throw new \Exception();
                });

                it("is reported as failure", function() {

                });
            });
            """
        When I run `dspec ExceptionInAfterEachSpec.php`
        Then the output should contain "1 of 1 examples failed"
