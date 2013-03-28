Feature: Disable defining the dspec global functions
    In order to prevent name clashes
    As a developer
    I need to prevent dspec loading the global functions

    Scenario: Globals disabled via env
        Given a file named "TestSpec.php" with:
            """php
            <?php

            use DSpec\DSpec as ds;

            function fail() {}

            ds::describe("test", function() {
                ds::it("should pass", function() {
                });
            });
            """
        When I run `DSPEC_NO_GLOBALS=1 dspec TestSpec.php`
        Then the output should contain "1 example passed"

