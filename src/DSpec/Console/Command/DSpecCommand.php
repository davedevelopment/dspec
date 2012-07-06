<?php

namespace DSpec\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
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
use DSpec\Container;
use DSpec\ExampleGroup;
use DSpec\DSpec as ds;
use DSpec\Events;
use DSpec\Event\SuiteStartEvent;
use DSpec\Event\SuiteEndEvent;
use DSpec\Formatter\Progress;
use DSpec\Provider\ConfigServiceProvider;

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
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('dspec')
            ->setDescription('run dspec')
            ->addArgument('specs', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Files/dirs to run')
            ->addOption('formatter', 'f', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Formatter to use');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            throw new \RunTimeException("dspec requires PHP >= 5.4.0");
        }

        $container = $this->container;

        $config = $container['profile'];

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
            }

            if (false !== $class) {
                $out = $output;
                if (isset($options->out)) {
                    $fh = fopen($options->out, 'a', false);
                    if (!$fh) {
                        throw new \InvalidArgumentException("Could not open {$options['out']}");
                    }
                    $out = new StreamOutput($fh);
                }

                $options = (array) $options;
                $formatter = (new $class($options))->setOutput($out);
            } else {
                if (isset($container[$f])) {
                    $formatter = $container[$f];
                }
                throw new \InvalidArgumentException("formatter:$f not found");
            }

            if (!($formatter instanceof \DSpec\Formatter\FormatterInterface)) {
                throw new \InvalidArgumentException(
                    get_class($formatter) . " needs to implement DSpec\Formatter\FormatterInterface"
                );
            }

            $formatters[] = $formatter;
        }

        foreach ($formatters as $formatter) {
            if ($formatter instanceof EventSubscriberInterface) {
                $container['dispatcher']->addSubscriber($formatter);
            }
        }

        // context and run
        $context    = new SpecContext();
        $suite      = new ExampleGroup($config->suite_name, $context);
        $reporter   = new Reporter($container['dispatcher']);

        ds::setContext($context);

        /**
         * The reporter could dispatch these events, assuming the example group 
         * alerts it to example group start/finish
         */    
        $container['dispatcher']->dispatch(Events::COMPILER_START, new Event());
        $context->load($files, $suite);
        $container['dispatcher']->dispatch(Events::COMPILER_END, new Event());

        $container['dispatcher']->dispatch(Events::SUITE_START, new SuiteStartEvent($suite));
        $suite->run($reporter);
        $container['dispatcher']->dispatch(Events::SUITE_END, new SuiteEndEvent($suite, $reporter));

        foreach($formatters as $f) {
            $f->format($reporter, $suite, $config->verbose);
        }

        return count($reporter->getFailures()) ? 1 : 0;
    }

}
