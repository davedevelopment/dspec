dspec
=====

`dspec` is a BDD style testing tool for PHP 5.4+. It's a little flaky. It's
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

    beforeEach(function() {                                  // run before every it() that follows, including in child describes/contexts
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

Execution Scope
---------------

By default, DSpec does not tear down the scope before each example, but you can
choose to during a before or after hook:

``` php
<?php

beforeEach(function() {
    $this->tearDown();
});

```

Using $this is totally optional, using regular variable binding with your closures
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
`DSpec\Compiler`, so you can also use `$this`, but I think it's a little ugly:

``` php
<?php 

$this->describe("something", function() {
    $this->context("when this", function() {
        $this->it("does this", function() {

        });
    });
});
```

Due to the way the `DSpec` class uses statics, the above method is
how the `DSpec\Compiler` is tested in `spec/DSpec/CompilerSpec.php`

Todo
----

* Config files
* Command line options
* Formatters
* Documentation

Copyright
---------

Copyright (c) 2012 Dave Marshall. See LICENCE for further details
