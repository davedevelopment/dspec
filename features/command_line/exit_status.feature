Feature: Runner exits with a relevant status
    In order to see if the tests passed or failed
    As a runner of the software
    I'd like to receive a relevant exit code

    Scenario: Passing tests give zero exit code
        Given a file named "OkSpec.php" with:
            """
            <?php
            describe("pass", function() {
                it("should pass", function() {
                });
            });
            """
        When I run `dspec OkSpec.php`
        Then the exit status should be 0

    Scenario: non-zero exit code for failure
        Given a file named "FailSpec.php" with:
            """
            <?php
            describe("fail", function() {
                it("should fail", function() {
                    throw new Exception();
                });
            });
            """
        When I run `dspec FailSpec.php`
        Then the exit status should not be 0

    Scenario: non-zero exit code for failure with pass
        Given a file named "FailSpec.php" with:
            """
            <?php
            describe("fail", function() {
                it("should fail", function() {
                    throw new Exception();
                });
                it("should pass", function() {
                });
            });
            """
        When I run `dspec FailSpec.php`
        Then the exit status should not be 0

    Scenario: non-zero exit with nested describes
        Given a file named "FailSpec.php" with:
            """
            <?php
            describe("passfail", function() {
                it("should pass", function() {
                });

                describe("fail", function() {
                    it("should fail", function() {
                        throw new Exception();
                    });
                });
            });
            """
        When I run `dspec FailSpec.php`
        Then the exit status should not be 0

    Scenario: Exit with 0 when no examples are run
        Given an empty file named "EmptySpec.php"
            """
            """
        When I run `dspec EmptySpec.php`
        Then the exit status should be 0
