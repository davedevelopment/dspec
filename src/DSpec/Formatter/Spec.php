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
use DSpec\Example;
use DSpec\Formatter\FailureTree;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Spec extends Summary implements FormatterInterface
{
    public function format(Reporter $r, ExampleGroup $suite, $verbosity = false)
    {
        $this->output->writeln("");
        $this->traverse($suite, $this->output, 0, $verbosity);
        $this->output->writeln("");

        $summary = (new Summary())->setOutput($this->output);
        $summary->format($r, $suite, $verbosity);
        $failureTree = (new FailureTree)->setOutput($this->output);
        $failureTree->format($r, $suite, $verbosity);

        $this->output->writeln("");
    }

    public function traverse($eg, $output, $indent, $verbosity) {

        foreach ($eg->getChildren() as $child) {
            if ($child instanceof \DSpec\ExampleGroup) {
                $output->writeln(str_repeat(" ", $indent) . $child->getTitle());
                $this->traverse($child, $output, $indent + 2, $verbosity);
                continue;
            }

            switch($child->getResult()) {
                case Example::RESULT_FAILED:
                    $format = "<dspec-bold-fail>✖</dspec-bold-fail> <dspec-fail>%s</dspec-fail>";
                    break;

                case Example::RESULT_PASSED:
                    $format = "<dspec-bold-pass>✔</dspec-bold-pass> <dspec-pass>%s</dspec-pass>";
                    break;

                case Example::RESULT_PENDING:
                    $format = "<dspec-bold-pending>-</dspec-bold-pending> <dspec-pending>%s</dspec-pending>";
                    break;

                default:
                case Example::RESULT_SKIPPED:
                    $format = "<dspec-bold-skipped>-</dspec-bold-skipped> <dspec-skipped>%s</dspec-skipped>";
                    break;
            }

            $output->write(str_repeat(" ", $indent));
            $output->write(sprintf($format, $child->getTitle()));
            $output->writeln(sprintf(" <dspec-meta>(%ss)</dspec-meta>", round($child->getTime(), 5)));
        }
    }
}
