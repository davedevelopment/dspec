<?php

namespace DSpec\Formatter;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use DSpec\Event\ExampleFailEvent;
use DSpec\Event\ExamplePassEvent;
use DSpec\Event\ExamplePendEvent;
use DSpec\Event\ExampleSkipEvent;
use DSpec\Events;
use DSpec\Reporter;
use DSpec\ExampleGroup;
use DSpec\Formatter\FormatterInterface;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Progress extends AbstractFormatter implements FormatterInterface
{
    /**
     * @var int
     */
    protected $counter = 0;

    static public function getSubscribedEvents()
    {
        return array_merge(parent::getSubscribedEvents(), array(
            Events::EXAMPLE_FAIL => array('onExampleFail', 0),
            Events::EXAMPLE_PASS => array('onExamplePass', 0),
            Events::EXAMPLE_PEND => array('onExamplePend', 0),
            Events::EXAMPLE_SKIP => array('onExampleSkip', 0),
        ));
    }

    public function format(Reporter $r, ExampleGroup $suite, $verbose = false)
    {
        $this->output->writeln("");
        $this->output->writeln("");
        $summary = (new Summary())->setOutput($this->output);
        $summary->format($r, $suite, $verbose);
        $failureTree = (new FailureTree)->setOutput($this->output);
        $failureTree->format($r, $suite, $verbose);
    }

    /**
     * @param ExampleFailEvent $e
     */
    public function onExampleFail(ExampleFailEvent $e)
    {
        $this->writeProgress("<dspec-fail>.</dspec-fail>");
    }


    /**
     * @param ExamplePassEvent $e
     */
    public function onExamplePass(ExamplePassEvent $e)
    {
        $this->writeProgress('<dspec-pass>.</dspec-pass>');
    }

    /**
     * @param ExamplePendEvent $e
     */
    public function onExamplePend(ExamplePendEvent $e)
    {
        $this->writeProgress('<dspec-pending>.</dspec-pending>');
    }

    /**
     * @param ExampleSkipEvent $e
     */
    public function onExampleSkip(ExampleSkipEvent $e)
    {
        $this->writeProgress('<dspec-skipped>.</dspec-skipped>');
    }

    /**
     * Write the progress dots and Fs
     *
     * @param string $string
     */
    protected function writeProgress($string)
    {
        $this->output->write($string, ++$this->counter % 80 == 0);
    }

}
