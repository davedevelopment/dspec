Feature: Specify formatters in the config file
    In order to get the output looking the way I want
    As a developer
    I need to specify the formatter

    Scenario: Specify one formatter
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        And a file named "dspec.yml" with:
            """
            default:
                formatters:
                    summary: ~
            """
        When I run `dspec TestSpec.php`
        Then the output should contain "1 of 2 examples failed"
        And the output should not contain ".."
        And the output should not contain "Failures:"

    Scenario: Specify two formatters 
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        And a file named "dspec.yml" with:
            """
            default:
                formatters:
                    summary: ~
                    failureTree: ~
            """
        When I run `dspec TestSpec.php `
        Then the output should contain "1 of 2 examples failed"
        And the output should contain "Failures:"
        And the output should not contain ".."

    Scenario: Specify an output file
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        And a file named "dspec.yml" with:
            """
            default:
                formatters:
                    summary: 
                        out: out.log
            """
        When I run `dspec TestSpec.php`
        Then the output should not contain "1 of 2 examples failed"
        And the file "out.log" should contain "1 of 2 examples failed"
                    
