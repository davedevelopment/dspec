<?php

namespace DSpec;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Events
{
    const EXAMPLE_FAIL = 'example.fail';
    const EXAMPLE_PASS = 'example.pass';
    const EXAMPLE_PEND = 'example.pend';
    const EXAMPLE_SKIP = 'example.skip';

    const COMPILER_START = 'compiler.start';
    const COMPILER_END   = 'compiler.end';

    const SUITE_START = 'suite.start';
    const SUITE_END   = 'suite.end';
}
