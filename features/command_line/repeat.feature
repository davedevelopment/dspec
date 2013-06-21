Feature: Repeat test runs

    Scenario: Specify the number of runs on the command line
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
            });
            """
        When I run `dspec --repeat 10 TestSpec.php`
        Then the output should contain "10 examples passed"
