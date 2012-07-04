<?php

namespace DSpec\Formatter;

use Symfony\Component\Console\Output\OutputInterface;
use DSpec\Reporter;
use DSpec\ExampleGroup;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface FormatterInterface 
{
    public function setOutput(OutputInterface $output);
    public function format(Reporter $r, ExampleGroup $suite, $verbose = false);
}
