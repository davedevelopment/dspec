Feature: Execute a php bootstrap file
    In order to bootstrap the test suite
    As a developer
    I want to specify a php file for bootstrapping

    Scenario: Specifying a bootstrap file 

        You can specify a file to be included before running your test suites via the command line switch `-b`. 
        
        Given a file named "bootstrap.php" with:
            """php
            <?php 
                echo 'dave';
            """
        When I run `dspec -b bootstrap.php`
        Then the output should contain "dave"

