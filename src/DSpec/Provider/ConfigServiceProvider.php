<?php

namespace DSpec\Provider;

use DSpec\Container;
use DSpec\Console\DSpecApplication;
use DSpec\ServiceProviderInterface;
use Symfony\Component\Yaml;
use ArrayObject;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class ConfigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        /**
         * The main config file
         */
        $container['config.path'] = null;

        $container['config.profile'] = 'default';

        $container['config.default_paths'] = array('dspec.yml', 'dspec.yml.dist', 'dspec/dspec.yml', 'dspec/dspec.yml.dist');

        $container['config.skeleton'] = array(
            'default' => array(
                'verbose' => false,
                'extensions' => array(),
                'formatters' => array(),
                'paths' => array(),
                'bootstrap' => false,
                'suite_name' => 'Suite',
            ),
        );

        $container['config.overrides'] = array();

        /**
         * Default parser
         */
        $container['config.parser'] = $container->share(function() {
            return new Yaml\Parser;
        });

        $container['config'] = function () use ($container) {

            $config = $container['config.skeleton'];

            if (!empty($container['config.path'])) {
                if (!file_exists($container['config.path'])) {
                    throw new \InvalidArgumentException("Could not find " . $container['config.path']);
                }

                $result = $container['config.parser']->parse(file_get_contents($container['config.path']));
                $config = static::configMerge($config, $result);
            } else {
                foreach ($container['config.default_paths'] as $path) {
                    if (file_exists($path)) {
                    $result = $container['config.parser']->parse(file_get_contents($path));
                        $config = static::configMerge($config, $result);
                    }
                }
            }

            return json_decode(json_encode($config));
        };

        $container['profile'] = function () use ($container) {

            $config = $container['config'];
            $profile = $config->default;

            if ($container['config.profile'] !== 'default') {
                if (!property_exists($config, $container['config.profile'])) {
                    throw new \InvalidArgumentException("profile:{$container['config.profile']} not found");
                }
                $profile = (object) array_merge((array) $profile, (array) $config->{$container['config.profile']});
            }

            // overrules

            if (isset($container['config.overrides']['bootstrap'])) {
                $profile->bootstrap = $container['config.overrides']['bootstrap'];
            }

            if (isset($container['config.overrides']['verbose'])) {
                $profile->verbose = $container['config.overrides']['verbose'];
            }

            /**
             * We can't set these in the defaults as we don't want them merging, 
             * only adding if nothing else present.
             */
            if (empty($profile->paths)) {
                $profile->paths[] = './spec';
            }

            if (empty($profile->formatters)) {
                $profile->formatters['progress'] = true;
            }

            return $profile;
        };

    }

    public function boot(DSpecApplication $app, Container $container)
    {
        /**
         * Lock these down now others have had chance to extend
         */
        $container['config'] = $container->share($container->extend('config', function($config) {
            return $config;
        }));

        $container['profile'] = $container->share($container->extend('profile', function($profile) {
            return $profile;
        }));
    }

    /**
     * Like array_merge_recursive, but good
     *
     * @see https://drupal.org/files/issues/array_merge_recursive_sucks.patch
     */
    public static function configMerge() {
        $result = array();
        $args = func_get_args();
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                continue;
            }
            foreach ($arg as $key => $value) {
                if (is_numeric($key)) {
                    // Renumber numeric keys as array_merge() does.
                    $result[] = $value;
                } elseif (array_key_exists($key, $result) && is_array($result[$key]) && is_array($value)) {
                    // Recurse only when both values are arrays.
                    $result[$key] = static::configMerge($result[$key], $value);
                } else {
                    // Otherwise, use the latter value.
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

}
