<?php

namespace DSpec\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\Finder;
use DSpec\Context\SpecContext;
use DSpec\Reporter;
use DSpec\ExampleGroup;
use DSpec\DSpec as ds;
use DSpec\Events;
use DSpec\Event\SuiteStartEvent;
use DSpec\Event\SuiteEndEvent;
use DSpec\Formatter\Progress;
use DSpec\Provider\ConfigServiceProvider;
use Cilex\Command\Command;
use Cilex\Provider\MonologServiceProvider;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DSpecCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dspec')
            ->setDescription('run dspec')
            ->addArgument('specs', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Files/dirs to run')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to a configuration file')
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Chosen configuration profile')
            ->addOption('bootstrap', 'b', InputOption::VALUE_REQUIRED, 'A php bootstrap file')
            ->addOption('formatter', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Formatter to use');

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();

        $app['dispatcher'] = new EventDispatcher;

        /**
         * Process options
         */
        $app->register(new ConfigServiceProvider());
            
        $app['config.path'] = $input->getOption('config');
        $app['config.default_paths'] = array('dspec.yml', 'dspec.yml.dist', 'dspec/dspec.yml', 'dspec/dspec.yml.dist');
        $app['config.defaults'] = array(
            'default' => array(
                'verbose' => $input->getOption('verbose'),
                'extensions' => array(),
                'formatters' => array(),
                'paths' => array(),
            ),
        );

        $profile = 'default';
        $config = $app['config']->$profile;;
        if ($input->getOption('profile') && $input->getOption('profile') !== 'default') {
            $profile = $input->getOption('profile');
            if (!property_exists($app['config'], $profile)) {
                throw new \InvalidArgumentException("profile:$profile not found");
            }
            $config = (object) array_merge((array) $config, (array) $app['config']->$profile);
        }

        /**
         * We can't set these in the defaults as we don't want them merging, 
         * only adding if nothing else present.
         */
        if (empty($config->paths)) {
            $config->paths[] = './spec';
        }

        if (empty($config->formatters)) {
            $config->formatters['progress'] = true;
        }

        $bootstrap = $input->getOption('bootstrap') ?: isset($config->bootstrap) ? $config->bootstrap : null;

        if ($bootstrap) { 
            $inc = function() use ($bootstrap) {
                require $bootstrap;
            };
            $inc();
        }

        // extensions
        foreach($config->extensions as $name => $options) {
            $class = "\\DSpec\\Provider\\" . ucfirst($name) . "ServiceProvider";
            if (!class_exists($class)) {
                $class = $name;
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException("class:$class not found");
                }
            } 
            $app->register(new $class, (array) $options);
        }

        // paths

        $paths = $input->getArgument('specs') ?: $config->paths;

        $files = array();
        foreach ($paths as $p) {

            if (!is_file($p) && !is_dir($p)) {
                $output->writeln("<error>Path not found: $p</error>");
            }

            if (is_file($p)) {
                $files[] = $p;
                continue;
            }

            if (is_dir($p)) {
                foreach ((new Finder)->in($p)->files()->name("*Spec.php") as $f) {
                    $files[] = $f->getRealPath();
                }
                continue;
            }
        }

        if (empty($files)) {
            $output->writeln("<comment>No specs found</comment>");
            return 0;
        }

        // formatters
        $formatters = array();
        $requestedFormatters = $config->formatters;

        if (count($input->getOption('formatter'))) {
            $requestedFormatters = array();
            foreach ($input->getOption('formatter') as $f) {
                $parts = explode(':', $f);
                $requestedFormatters[$parts[0]] = isset($parts[1]) ? (object)['out' => $parts[1]] : (object)[];
            }
        }

        foreach($requestedFormatters as $f => $options) {

            $class = false;
            if (class_exists($f)) {
                $class = $f;
            } else if (class_exists('\\DSpec\\Formatter\\' . ucfirst($f))) {
                $class = '\\DSpec\\Formatter\\' . ucfirst($f);
            } else {
                throw new \InvalidArgumentException("formatter:$f not found");
            }

            $out = $output;
            if (isset($options->out)) {
                $fh = fopen($options->out, 'a', false);
                if (!$fh) {
                    throw new \InvalidArgumentException("Could not open {$options['out']}");
                }
                $out = new StreamOutput($fh);
            }

            $options = (array) $options;
            $formatters[] = (new $class($options))->setOutput($out);
        }

        foreach ($formatters as $formatter) {
            if ($formatter instanceof EventSubscriberInterface) {
                $app['dispatcher']->addSubscriber($formatter);
            }
        }

        // context and run
        $context    = new SpecContext();
        $suite      = new ExampleGroup("Suite", $context);
        $reporter   = new Reporter($app['dispatcher']);

        ds::setContext($context);

        /**
         * The reporter could dispatch these events, assuming the example group 
         * alerts it to example group start/finish
         */    
        $app['dispatcher']->dispatch(Events::COMPILER_START, new Event());
        $context->load($files, $suite);
        $app['dispatcher']->dispatch(Events::COMPILER_END, new Event());

        $app['dispatcher']->dispatch(Events::SUITE_START, new SuiteStartEvent($suite));
        $suite->run($reporter);
        $app['dispatcher']->dispatch(Events::SUITE_END, new SuiteEndEvent($suite, $reporter));

        foreach($formatters as $f) {
            $f->format($reporter, $suite, $config->verbose);
        }

        return count($reporter->getFailures()) ? 1 : 0;
    }
}
