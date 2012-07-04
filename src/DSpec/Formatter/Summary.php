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

class Summary extends AbstractFormatter implements FormatterInterface
{
    /**
     * @var float
     */
    protected $startTime = 0;

    public function __construct(array $options = array())
    {
        /**
         * Force the start time, in the event events aren't used
         */
        if (isset($options['startTime'])) {
            $this->startTime = $options['startTime'];
        }
    }

    static public function getSubscribedEvents()
    {
        return array(
            Events::COMPILER_START => array('onCompilerStart', 0),
        );
    }

    public function onCompilerStart(Event $e)
    {
        $this->startTime = microtime(true);
    }

    public function format(Reporter $r, ExampleGroup $suite, $verbose = false)
    {
        $duration = microtime(true) - $this->startTime;

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
    }

}
