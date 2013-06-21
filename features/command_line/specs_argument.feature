Feature: Specifying which specs to run
    In order to run the specs I want
    As a spec runner
    I want to specify which specs to run

    Scenario: Default to *Spec.php within a ./spec directory
        Given a file named "spec/TestSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/Test.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec`
        Then the output should contain "1 example passed"

    Scenario: Specify a directory
        Given a file named "spec/TestSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/Test.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec`
        Then the output should contain "1 example passed"


    Scenario: Specifying multiple directories
        Given a file named "spec/TestSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec2/TestSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec spec2`
        Then the output should contain "1 of 2 examples failed"

    Scenario: Specifying directories and files at the same time
        Given a file named "spec/TestSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec2/Test.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec spec2/Test.php`
        Then the output should contain "1 of 2 examples failed"

    Scenario: Specifying a glob
        Given a file named "spec/IncludeSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
            });
            });
            """
        And a file named "spec/ExcludeSpec.php" with:
            """php
            <?php
            describe("a thing", function() {
            it("does things", function() {
                throw new Exception();
            });
            });
            """
        When I run `dspec spec/Include*`
        Then the output should contain "1 example passed"
