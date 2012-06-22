Feature: before and after hooks

    Use `before` and `after` hooks to execute arbitrary code before and/or
    after the body of an example is run:

    Scenario: define beforeEach block
        Given a file named "BeforeEachSpec.php" with:
            """
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

    Scenario: before/after blocks are run in order
        Given a file named "BeforeAfterOrderSpec.php" with:
            """
            <?php
            describe("before and after callbacks", function() {
                beforeEach(function() {
                    echo "before each\n";
                });

                beforeEach(function() {
                    echo "before each\n";
                });

                afterEach(function() {
                    echo "after each\n";
                });

                it("gets run in order", function() {
                    echo "it\n";
                });
            });
        """
        When I run `dspec BeforeAfterOrderSpec.php`
        Then the output should contain:
        """
        before each
        before each
        it
        after each
        """

    Scenario: exception in beforeEach is captured and reported as failure
        Given a file named "ExceptionInBeforeEachSpec.php" with:
            """
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


    Scenario: exception in afterEach is captured and reported as failure
        Given a file named "ExceptionInAfterEachSpec.php" with:
            """
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
