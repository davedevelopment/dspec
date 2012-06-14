<?php

namespace DSpec\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\Finder;
use DSpec\Compiler;
use DSpec\Reporter;
use DSpec\ExampleGroup;
use DSpec\DSpec as ds;
use DSpec\Events;
use DSpec\Event\SuiteStartEvent;
use DSpec\Event\SuiteEndEvent;
use DSpec\Formatter\Progress;

class DSpecCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('dspec')
            ->setDescription('run dspec')
            ->addArgument('specs', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Files/dirs to run')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to a configuration file');
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getArgument('specs');

        if (empty($paths)) {
            $paths = array("./spec");
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
        $suite      = new ExampleGroup("Suite", function() {});
        $compiler   = new Compiler($suite, $dispatcher);
        $reporter   = new Reporter($dispatcher);
        $formatter  = new Progress($output);

        $dispatcher->addSubscriber($formatter);
        ds::setCompiler($compiler);

        /**
         * The reporter could dispatch these events, assuming the example group 
         * alerts it to example group start/finish
         */    
        $dispatcher->dispatch(Events::COMPILER_START, new Event());
        $exampleGroup = $compiler->compile($files);
        $dispatcher->dispatch(Events::COMPILER_END, new Event());

        $dispatcher->dispatch(Events::SUITE_START, new SuiteStartEvent($exampleGroup));
        $exampleGroup->run($reporter);
        $dispatcher->dispatch(Events::SUITE_END, new SuiteEndEvent($exampleGroup, $reporter));

        return count($reporter->getFailures()) ? 1 : 0;
    }
}
