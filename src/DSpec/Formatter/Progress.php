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

    /**
     * @var float
     */
    protected $startTime = 0;

    static public function getSubscribedEvents()
    {
        return array(
            Events::EXAMPLE_FAIL => array('onExampleFail', 0),
            Events::EXAMPLE_PASS => array('onExamplePass', 0),
            Events::EXAMPLE_PEND => array('onExamplePend', 0),
            Events::EXAMPLE_SKIP => array('onExampleSkip', 0),
            Events::COMPILER_START => array('onCompilerStart', 0),
        );
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
     * @param Event
     */
    public function onCompilerStart(Event $e)
    {
        $this->startTime = microtime(true);
    }

    /**
     * @param Event
     */
    public function format(Reporter $r, ExampleGroup $suite)
    {
        $duration = microtime(true) - $this->startTime;
        $this->output->writeln("");
        $this->output->writeln("");

        $total        = $suite->total();
        $failures     = $r->getFailures();
        $failureCount = count($failures);
        $passCount    = count($r->getPasses());
        $format       = $failureCount > 0 ? 'error' : 'info';

        if ($failureCount) {
            $resultLine = sprintf(
                "<dspec-bold-fail>✖</dspec-bold-fail> <dspec-fail>%d of %d examples failed</dspec-fail>", 
                $failureCount,
                $total
            );

        } else {
            $resultLine = sprintf(
                "<dspec-bold-pass>✔</dspec-bold-pass> <dspec-pass>%d example%s passed</dspec-pass>", 
                $passCount,
                $passCount != 1 ? 's' : ''
            );
        }

        if (count($r->getPending())) {
            $resultLine.= sprintf(
                ", <dspec-pending>%d pending</dspec-pending>", 
                count($r->getPending())
            );
        }

        if (count($r->getSkipped())) {
            $resultLine.= sprintf(
                ", <dspec-skipped>%d skipped</dspec-skipped>", 
                count($r->getSkipped())
            );
        }


        $this->output->writeln(sprintf("%s <dspec-meta>(%ss)</dspec-meta>", $resultLine, round($duration, 5)));

        $failureTree = (new FailureTree)->setOutput($this->output);
        $failureTree->format($r, $suite);
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
