Feature: Clean context per example
    In order to help my tests be more readable
    As a developer 
    I want the to describe the setup of individual vars

    Background:
        Given a file named "dspec.yml" with:
            """
            default:
                extensions:
                    hamcrest: 
                        hamcrest.globals: true
            """

    @php5.4
    Scenario: Let var is available on $this
        Given a file named "context.feature" with:
            """
            <?php
            describe("top level", function() {

                let("obj", function() {
                    return new stdClass;
                });

                it("should be a stdClass", function() {
                    assertThat($this->obj, anInstanceOf("stdClass"));
                });

            });
            """
        When I run `dspec context.feature`
        Then the output should contain "1 example passed"

    Scenario: Let var is can be injected by name
        Given a file named "context.feature" with:
            """
            <?php
            describe("top level", function() {

                let("obj", function() {
                    return new stdClass;
                });

                it("should be a stdClass", function($obj) {
                    assertThat($obj, anInstanceOf("stdClass"));
                });

            });
            """
        When I run `dspec context.feature`
        Then the output should contain "1 example passed"

    @php5.4
    Scenario: Let var is memoized
        Given a file named "context.feature" with:
            """
            <?php
            describe("top level", function() {

                let("obj", function() {
                    return new stdClass;
                });

                it("should be a stdClass", function() {
                    assertThat($this->obj, identicalTo($this->obj));
                });

            });
            """
        When I run `dspec context.feature`
        Then the output should contain "1 example passed"

    @php5.4
    Scenario: Let var can use other lets
        Given a file named "context.feature" with:
            """
            <?php
            describe("top level", function() {

                let("obj", function() {
                    $obj  = new stdClass;
                    $obj->other = $this->otherObj;
                    return $obj;
                });

                let("otherObj", function() {
                    return new stdClass;
                });

                it("should be a stdClass", function() {
                    assertThat($this->obj->other, identicalTo($this->otherObj));
                });

            });
            """
        When I run `dspec context.feature`
        Then the output should contain "1 example passed"

    Scenario: Let var can use other lets via injection
        Given a file named "context.feature" with:
            """
            <?php
            describe("top level", function() {

                let("obj", function($otherObj) {
                    $obj  = new stdClass;
                    $obj->other = $otherObj;
                    return $obj;
                });

                let("otherObj", function() {
                    return new stdClass;
                });

                it("should be a stdClass", function($obj, $otherObj) {
                    assertThat($obj->other, identicalTo($otherObj));
                });

            });
            """
        When I run `dspec context.feature`
        Then the output should contain "1 example passed"
