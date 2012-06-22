Feature: specs argument
    In order to run the specs I want
    As a spec runner
    I want to specify which specs to run

    Scenario: Default to ./spec 
        Given a file named "spec/TestSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/Test.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example passed"

    Scenario: Pass a directory 
        Given a file named "spec/TestSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/Test.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec`
        Then the output should contain "1 example passed"


    Scenario: Pass multiple directories
        Given a file named "spec/TestSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec2/TestSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec spec2`
        Then the output should contain "1 of 2 examples failed"

    Scenario: Pass a dir and a file
        Given a file named "spec/TestSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec2/Test.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec spec2/Test.php`
        Then the output should contain "1 of 2 examples failed"

    Scenario: Pass a glob
        Given a file named "spec/IncludeSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/ExcludeSpec.php" with:
            """
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec/Include*`
        Then the output should contain "1 example passed"
