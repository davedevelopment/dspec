Feature: Skipped examples
    In order to write specs that are conditionally run
    As a developer
    I want to mark examples as skipped

    Scenario: Mark an example as skipped with exception
        Given a file named "spec/SkippedSpec.php" with:
            """
            <?php

            describe("something", function() {
                it("does things", function() {
                    throw new DSpec\Exception\SkippedExampleException();
                });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example, 0 failures, 1 skipped"

    Scenario: Mark an example as skipped with function
        Given a file named "spec/SkippedSpec.php" with:
            """
            <?php

            describe("something", function() {
                it("does things", function() {
                    skip();
                });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example, 0 failures, 1 skipped"

