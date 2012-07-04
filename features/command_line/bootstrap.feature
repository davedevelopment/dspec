Feature: Execute a php bootstrap file
    In order to bootstrap the test suite
    As a developer
    I want to specify a php file for bootstrapping

    Scenario: User specifies bootstrap file
        Given a file named "bootstrap.php" with:
            """
            <?php 
                echo 'dave';
            """
        When I run `dspec -b bootstrap.php`
        Then the output should contain "dave"

