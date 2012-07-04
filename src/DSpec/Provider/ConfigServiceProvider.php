<?php

namespace DSpec\Provider;

use Cilex\Application;
use Cilex\ServiceProviderInterface;
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
    public function register(Application $app)
    {
        /**
         * The main config file
         */
        $app['config.path'] = null;

        /**
         * If `config.path` is not set, these paths will be searched, first one 
         * found gets loaded
         */
        $app['config.default_paths'] = array();

        /**
         * These paths will be merged in to the config, if they are found
         */
        $app['config.merge_paths'] = array();

        /**
         * Default config object 
         */
        $app['config.defaults'] = array();

        /**
         * Default parser
         */
        $app['config.parser'] = $app->share(function() {
            return new Yaml\Parser;
        });

        $app['config'] = $app->share(function () use ($app) {

            $config = $app['config.defaults'];

            if (!empty($app['config.path'])) {
                if (!file_exists($app['config.path'])) {
                    throw new \InvalidArgumentException("Could not find " . $app['config.path']);
                }

                $result = $app['config.parser']->parse(file_get_contents($app['config.path']));
                $config = static::configMerge($config, $result);
            } else {
                foreach ($app['config.default_paths'] as $path) {
                    if (file_exists($path)) {
                    $result = $app['config.parser']->parse(file_get_contents($path));
                        $config = static::configMerge($config, $result);
                    }
                }
            }

            foreach($app['config.merge_paths'] as $path) {
                if (file_exists($path)) {
                    $result = $app['config.parser']->parse(file_get_contents($path));
                    $config = static::configMerge($config, $result);
                }
            }

            return json_decode(json_encode($config));
        });
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
