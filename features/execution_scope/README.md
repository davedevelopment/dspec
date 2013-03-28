Execution Scope
---------------

The closures in the various methods get bound to a Context object, which is
cloned throughout the test run, in an attempt to share context where
appropriate, but allow each example a clean version of that shared context. This
is a last resort though, proper setup and tear down in beforeEach and afterEach
hooks should provide adequate isolation.

``` php
<?php

describe("Context", function() {

    $this->objA = new stdClass;

    it("has acccess to objA", function() {
        if (!isset($this->objA)) {
            throw new Exception("Could not access objA");
        }

        $this->objB = new stdClass;
    });

    it("does not have access to objB", function() {
        if (isset($this->objB)) {
            throw new Exception("Could access objB");
        }
    });
});

``` 

Using $this is totally optional, in PHP 5.3 you can use `let` and injection to pass vars around:

``` php
<?php

describe("test", function() {
    let("objA", function() {
        return new stdClass;
    });

    it("has acccess to objA", function($objA) {
        assertThat($objA, anInstanceOf("stdClass"));
    });
});
```

Using regular variable binding with your closures
is an alternative option, but can be a bit messier;

``` php
<?php

describe("Connection", function() {

    $db = new Connection();

    $dave = new User();
    $bex  = new User();

    beforeEach(function() use ($db, $dave, $bex) {
        $db->truncate('users');                
        $db->save($dave);
        $db->save($bex);
    });

    context("#findAll()", function() use ($db) {
        it("responds with all records", function() use ($db) {
            $users = $db->find('users');
            assertThat(count($users), equalTo(2));
        });
    });

});
```
