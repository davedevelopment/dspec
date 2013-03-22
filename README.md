dspec
=====

[![Build Status](https://secure.travis-ci.org/davedevelopment/dspec.png?branch=master)](http://travis-ci.org/davedevelopment/dspec)

`dspec` is a BDD style testing tool for PHP 5.3. It's a little flaky. It's
meant to be like the awesome [mocha](http://visionmedia.github.com/mocha/), but
obviously not that good. There aren't any matchers, I use
[Hamcrest](http://code.google.com/p/hamcrest/source/browse/trunk/hamcrest-php/)
for the that, if you want mocks and stubs, use
[Mockery](http://github.com/padraic/mockery).

Installation
------------

The only documented way to install `dspec` is with
[Composer](http://getcomposer.org)

``` bash
$ composer.phar require --dev davedevelopment/dspec:*
```

Usage
-----

Create a Spec file

``` php
<?php

// BowlingSpec.php

require_once 'Bowling.php';
require_once 'hamcrest/Hamcrest.php';

describe("Bowling", function() {

    beforeEach(function() {                                  // run before every sibling and descendant it() 
        $this->bowling = new Bowling;                        // PHP 5.4's closure binding allows the use of this
    }); 

    it("should score zero for gutter game", function() {
        for ($i = 0; $i < 20; $i++) {
            $this->bowling->hit(0);
        }

        assertThat($this->bowling->score, equalTo(0));      // hamcrest assertion
    });

});

```

Run it 

``` bash
$ vendor/bin/dspec BowlingSpec.php
```

For more examples, checkout the `features` or the `spec` dirs.

Config
------

There are a few command line switches, `dspec --help` will show you those. You
can also use a configuration file, by specifying one on the command line, or by
having a file name `dspec.yml` or `dspec.yml.dist` in the current working
directory. Checkout the projects `dspec.yml.dist` for some examples of
configuration.

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

Global functions
----------------

If global functions aren't your thing, you can use class methods of the DSpec
class:

``` php
<?php

use DSpec\DSpec as ds;

ds::describe("something", function() {
    ds::context("when this", function() {
        ds::it("does this", function() {

        });
    });
});

```

Alternatively, DSpec includes your test files within the scope of the
`DSpec\Context\SpecContext`, so you can also use `$this`, but I think it's a little ugly:

``` php
<?php 

$this->describe("something", function() {
    $this->context("when this", function() {
        $this->it("does this", function() {

        });
    });
});
```

Todo
----

* More tests
* Logging would be nice for debugging
* Documentation
* Profile and improve memory consumption

Contributing
------------

Check the todo list above, there's a good chance I'm already working on those.
Fork, branch, write tests, write code, refactor, repeat, pull request. 

Copyright
---------

Copyright (c) 2012 Dave Marshall. See LICENCE for further details
