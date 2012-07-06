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

class FailureTree extends AbstractFormatter implements FormatterInterface
{
    public function format(Reporter $r, ExampleGroup $suite, $verbosity = false)
    {
        if ($suite->hasFailures()) {
            $this->output->writeln("");
            $this->output->writeln("<dspec-fail>Failures</dspec-fail>:");
            $this->output->writeln("");

            /**
             * I'm a bad man
             */
            $outputter = function($eg, $output, $callback, $indent) use ($verbosity) {
                if (!$eg->hasFailures()) {
                    return;
                }

                $output->writeln(str_repeat(" ", $indent) . $eg->getTitle());
                foreach ($eg->getChildren() as $child) {
                    if ($child instanceof \DSpec\ExampleGroup) {
                        $callback($child, $output, $callback, $indent + 2);
                        continue;
                    }

                    if (!$child->isFailure()) {
                        continue;
                    }

                    $output->writeln(sprintf(
                        "%s<dspec-bold-fail>✖</dspec-bold-fail> <dspec-fail>%s</dspec-fail>",
                        str_repeat(" ", $indent + 2),
                        $child->getTitle()
                    ));

                    $e = $child->getFailureException();

                    if ($verbosity) {
                        $failureMessage = (string) $e;
                    } else {
                        $failureMessage = $e->getMessage();
                    }

                    $lines = explode("\n", $failureMessage);
                    foreach ($lines as $n => $line) {
                        $lines[$n] = str_repeat(" ", $indent + 4) . $line;
                    }
                    $output->writeln(implode("\n", $lines));
                }
            };

            $outputter($suite, $this->output, $outputter, 0);
        }
    }
}
