Feature: Pending examples
    In order to write specs without full implementing them
    As a developer
    I want to mark examples as pending

    Scenario: Mark an example as pending with exception
        Given a file named "spec/PendingSpec.php" with:
            """
            <?php

            describe("something", function() {
                it("does things", function() {
                    throw new DSpec\Exception\PendingExampleException();
                });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example, 0 failures, 1 pending"

    Scenario: Mark an example as pending with function
        Given a file named "spec/PendingSpec.php" with:
            """
            <?php

            describe("something", function() {
                it("does things", function() {
                    pending();
                });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example, 0 failures, 1 pending"

