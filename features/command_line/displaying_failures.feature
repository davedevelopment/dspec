Feature: Displaying failures
    In order to fix my code
    As a developer
    I need to see the details of the failed tests

    Scenario: Display Failure exception messages
        Given a file named "FailSpec.php" with:
            """php
            <?php

            describe("fail1", function() {
                it("will fail2", function() {
                    throw new Exception("MY Failure Message");
                });
            });
            """
        When I run `dspec FailSpec.php`
        Then the output should contain "MY Failure Message"
        And the output should contain "will fail2"
        And the output should contain "fail1"

    Scenario: Display stack trace in verbose mode
        Given a file named "FailSpec.php" with:
            """php
            <?php

            describe("fail1", function() {
                it("will fail2", function() {
                    throw new Exception("MY Failure Message");
                });
            });
            """
        When I run `dspec -v FailSpec.php`
        Then the output should contain "MY Failure Message"
        And the output should contain "will fail2"
        And the output should contain "fail1"
        And the output should contain "FailSpec.php:5"

