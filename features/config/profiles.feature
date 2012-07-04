Feature: Configuration profiles
    In order to run dspec differently under different circumstances
    As a developer
    I need to specify profiles in my configuration file

    Background:
        Given a file named "DaveSpec.php" with:
            """
            <?php

            describe("config file", function() {
                it("prints something", function() {});
            });

            """

    Scenario: Profile inherits default
        Given a file named "dspec.yml" with:
            """
            default:
                paths: [ "DaveSpec.php" ]
            dave: ~
            """
        When I run `dspec -p dave`
        Then the output should contain "1 example passed"

    Scenario: Profile overrides default
        Given a file named "dspec.yml" with:
            """
            default:
                paths: [ "DaveSpec.php" ]
            dave:
                paths: [ "BexSpec.php" ]

            """
        And a file named "BexSpec.php" with:
            """
            <?php

            describe("config file", function() {
                it("prints something", function() {});
                it("prints something", function() {});
            });

            """
        When I run `dspec -p dave`
        Then the output should contain "2 examples passed"

    Scenario: Command can not find profile
        When I run `dspec -p asdasdasas`
        Then the output should contain "profile:asdasdasas not found"
