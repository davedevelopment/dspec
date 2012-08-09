Feature: Pretty print specs and results
    In order to see the what is being specified
    As a developer
    I want to see the titles, context and grouping in the results

    Scenario: Shows a passing test
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("Dog", function() {
                describe("#bark()", function() {
                    it("should make a noise", function() {});
                });
            });
            """
        When I run `dspec -f spec TestSpec.php`
        Then the output should contain "1 example passed"
        And the output should contain "Dog"
        And the output should contain "#bark()"
        And the output should contain "should make a noise"

