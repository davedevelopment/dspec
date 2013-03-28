Installation
------------

The only documented way to install `dspec` is with [Composer](http://getcomposer.org)

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

