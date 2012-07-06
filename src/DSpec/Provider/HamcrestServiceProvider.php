<?php

namespace DSpec\Provider;

use DSpec\Console\DSpecApplication;
use DSpec\Container;
use DSpec\ServiceProviderInterface;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class HamcrestServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['hamcrest.globals'] = true;
        $container['hamcrest.path'] = null;
    }

    public function boot(DSpecApplication $app, Container $container) 
    {
        if (!$container['hamcrest.globals']) {
            return;
        }

        if ($container['hamcrest.path'] !== null) {
            if (file_exists($container['hamcrest.path'].'/hamcrest/Hamcrest.php')) {
                require_once $container['hamcrest.path'].'/hamcrest/Hamcrest.php';
            } else {
                throw new \InvalidArgumentException(
                    "Could not find hamcrest/Hamcrest.php under {$container['hamcrest.path']}"
                );
            }
        }

        if (file_exists(__DIR__.'/../../../../hamcrest-php/hamcrest/Hamcrest.php')) {
            // composer install
            require_once __DIR__.'/../../../../hamcrest-php/hamcrest/Hamcrest.php';
        } else if (file_exists(__DIR__.'/../../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php')) {
            // dspec dev
            require_once __DIR__.'/../../../vendor/davedevelopment/hamcrest-php/hamcrest/Hamcrest.php';
        } else if (fopen('hamcrest/Hamcrest.php', 'r', true)) {
            // include path
            require_once 'hamcrest/Hamcrest.php';
        } else {
            throw new \RunTimeException(
                "Could not find hamcrest, please install with composer or put on include path"
            );
        }
    }
}
