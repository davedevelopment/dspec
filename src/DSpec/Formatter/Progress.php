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

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Progress implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var float
     */
    protected $startTime = 0;

    /**
     * Constructor
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        $this->output->getFormatter()->setStyle('progress-fail', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('progress-bold-fail', new OutputFormatterStyle('red', null, array('bold')));
        $this->output->getFormatter()->setStyle('progress-pending', new OutputFormatterStyle('blue'));
        $this->output->getFormatter()->setStyle('progress-skipped', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('progress-pass', new OutputFormatterStyle('green'));
        $this->output->getFormatter()->setStyle('progress-bold-pass', new OutputFormatterStyle('green', null, array('bold')));
        $this->output->getFormatter()->setStyle('progress-meta', new OutputFormatterStyle('white', null, array()));
    }

    static public function getSubscribedEvents()
    {
        return array(
            Events::EXAMPLE_FAIL => array('onExampleFail', 0),
            Events::EXAMPLE_PASS => array('onExamplePass', 0),
            Events::EXAMPLE_PEND => array('onExamplePend', 0),
            Events::EXAMPLE_SKIP => array('onExampleSkip', 0),
            Events::SUITE_END    => array('onSuiteEnd', 0),
            Events::COMPILER_START => array('onCompilerStart', 0),
        );
    }

    /**
     * @param ExampleFailEvent $e
     */
    public function onExampleFail(ExampleFailEvent $e)
    {
        $this->writeProgress("<progress-fail>.</progress-fail>");
    }


    /**
     * @param ExamplePassEvent $e
     */
    public function onExamplePass(ExamplePassEvent $e)
    {
        $this->writeProgress('<progress-pass>.</progress-pass>');
    }

    /**
     * @param ExamplePendEvent $e
     */
    public function onExamplePend(ExamplePendEvent $e)
    {
        $this->writeProgress('<progress-pending>.</progress-pending>');
    }

    /**
     * @param ExampleSkipEvent $e
     */
    public function onExampleSkip(ExampleSkipEvent $e)
    {
        $this->writeProgress('<progress-skipped>.</progress-skipped>');
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
    public function onSuiteEnd(Event $e)
    {
        $duration = microtime(true) - $this->startTime;
        $this->output->writeln("");
        $this->output->writeln("");

        $total        = $e->getExampleGroup()->total();
        $r            = $e->getReporter();
        $failures     = $r->getFailures();
        $failureCount = count($failures);
        $passCount    = count($r->getPasses());
        $format       = $failureCount > 0 ? 'error' : 'info';

        if ($failureCount) {
            $resultLine = sprintf(
                "<progress-bold-fail>✖</progress-bold-fail> <progress-fail>%d of %d examples failed</progress-fail>", 
                $failureCount,
                $total
            );

        } else {
            $resultLine = sprintf(
                "<progress-bold-pass>✔</progress-bold-pass> <progress-pass>%d example%s passed</progress-pass>", 
                $passCount,
                $passCount != 1 ? 's' : ''
            );
        }

        if (count($r->getPending())) {
            $resultLine.= sprintf(
                ", <progress-pending>%d pending</progress-pending>", 
                count($r->getPending())
            );
        }

        if (count($r->getSkipped())) {
            $resultLine.= sprintf(
                ", <progress-skipped>%d skipped</progress-skipped>", 
                count($r->getSkipped())
            );
        }


        $this->output->writeln(sprintf("%s <progress-meta>(%ss)</progress-meta>", $resultLine, round($duration, 5)));

        $groups = array();

        /**
         * These need grouping by describe/context
         */
        foreach($failures as $f) {
            $indent = 0;
            foreach($f->getAncestors() as $eg) {
                $this->output->writeln(sprintf(
                    "<comment>%s%s</comment>",
                    str_repeat(" ", $indent),
                    $eg->getTitle()
                ));
                $indent+=2;
            }

            $this->output->writeln(sprintf(
                "<comment>%s%s</comment>",
                str_repeat(" ", $indent),
                $f->getFailureException()->getMessage() ?: "{no message}"
            ));
        }

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
