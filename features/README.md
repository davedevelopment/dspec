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

