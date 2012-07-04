Feature: Configuration files
    In order to run dspec in a consistent manner
    As a developer
    I need to specify a configuration file

    Background:
        Given a file named "DaveSpec.php" with:
            """
            <?php

            describe("config file", function() {
                it("prints something", function() {});
            });

            """

    Scenario: User specifies config file 
        Given a file named "mydspec.yml" with:
            """
            default:
                paths: [ "DaveSpec.php" ]
            """
        When I run `dspec -c mydspec.yml`
        Then the output should contain "1 example passed"

    Scenario: Command find default file
        Given a file named "dspec.yml" with:
            """
            default:
                paths: [ "DaveSpec.php" ]
            """
        When I run `dspec`
        Then the output should contain "1 example passed"

    Scenario: Command can not find specified file
        When I run `dspec -c asdasdasas`
        Then the output should contain "Could not find asdasdasas"
