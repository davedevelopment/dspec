<?php

namespace DSpec\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
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
            ->addOption('profile', 'p', InputOption::VALUE_REQUIRED, 'Chosen configuration profile');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Process options
         */
        $log = $this->getContainer()->register(new ConfigServiceProvider());

        $app = $this->getContainer();
            
        $app['config.path'] = $input->getOption('config');
        $app['config.default_paths'] = array('dspec.yml', 'dspec.yml.dist', 'dspec/dspec.yml', 'dspec/dspec.yml.dist');
        $app['config.defaults'] = array(
            'default' => array(
                'verbose' => $input->getOption('verbose'),
                'extensions' => array(),
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

        // extensions
        foreach($config->extensions as $class => $options) {
            $app->register(new $class, (array) $options);
        }

        // paths

        $paths = $input->getArgument('specs');
        if (empty($paths)) {
            $paths = isset($config->paths) 
                ? (array) $config->paths
                : array("./spec");
        }

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

        $dispatcher = new EventDispatcher;
        $context    = new SpecContext();
        $suite      = new ExampleGroup("Suite", $context);
        $reporter   = new Reporter($dispatcher);
        $formatter  = (new Progress())->setOutput($output);

        $dispatcher->addSubscriber($formatter);
        ds::setContext($context);

        /**
         * The reporter could dispatch these events, assuming the example group 
         * alerts it to example group start/finish
         */    
        $dispatcher->dispatch(Events::COMPILER_START, new Event());
        $context->load($files, $suite);
        $dispatcher->dispatch(Events::COMPILER_END, new Event());

        $dispatcher->dispatch(Events::SUITE_START, new SuiteStartEvent($suite));
        $suite->run($reporter);
        $dispatcher->dispatch(Events::SUITE_END, new SuiteEndEvent($suite, $reporter));

        $formatter->format($reporter, $suite, $input->getOption('verbose'));

        return count($reporter->getFailures()) ? 1 : 0;
    }
}
