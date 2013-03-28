Feature: Clean context per example
    In order to help isolate tests
    As a developer
    I want the execution context to be clean for every example

    @php5.4
    Scenario: Context is not tainted by previous examples
        Given a file named "context.feature" with:
            """php
            <?php
            describe("top level", function() {

                it("should pass", function() {
                    $this->obj = new stdClass;
                });

                it("should also pass", function() {
                    if (isset($this->obj)) {
                        fail();
                    }
                });
            });
            """
        When I run `dspec context.feature`
        Then the output should contain "2 examples passed"
