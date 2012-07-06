<?php

namespace DSpec\Provider;

use DSpec\Console\DSpecApplication;
use DSpec\Container;
use DSpec\ServiceProviderInterface;

use DSpec\Hook;
use DSpec\Events;
use DSpec\Event\SuiteStartEvent;
use Mockery as m;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class MockeryServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
    }

    public function boot(DSpecApplication $app, Container $container) 
    {
        $container['dispatcher']->addListener(Events::SUITE_START, function(SuiteStartEvent $e) {
            $e->getExampleGroup()->add(new Hook('afterEach', function() {
                m::close();
            }));
        });
    }
}
