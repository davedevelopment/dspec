Feature: Configuration files
    In order to run dspec in a consistent manner
    As a developer
    I need to specify a configuration file

    Background:
        Given a file named "DaveSpec.php" with:
            """php
            <?php

            describe("config file", function() {
                it("prints something", function() {});
            });

            """

    Scenario: Using the default config file

        By default, dspec will look for a file called `dspec.yml` or `dspec.yml.dist` in the current working directory.

        Given a file named "dspec.yml" with:
            """yaml
            default:
                paths: [ "DaveSpec.php" ]
            """
        When I run `dspec`
        Then the output should contain "1 example passed"

    Scenario: Specifying a config file on the command line

        You can use the `-c` switch to specify a config file to use.
        
        Given a file named "mydspec.yml" with:
            """yaml
            default:
                paths: [ "DaveSpec.php" ]
            """
        When I run `dspec -c mydspec.yml`
        Then the output should contain "1 example passed"

    Scenario: Command can not find specified file
        When I run `dspec -c asdasdasas`
        Then the output should contain "Could not find asdasdasas"
