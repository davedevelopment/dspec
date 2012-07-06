<?php

namespace DSpec;

use DSpec\Console\DSpecApplication;
use DSpec\Container;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

interface ServiceProviderInterface
{
    public function register(Container $container);

    public function boot(DSpecApplication $app, Container $container);
}
