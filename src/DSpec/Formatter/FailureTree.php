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
        if (!$suite->hasFailures()) {
            return;
        }
        $this->output->writeln("");
        $this->output->writeln("<dspec-fail>Failures</dspec-fail>:");
        $this->output->writeln("");

        static::traverse($suite, $this->output, 0, $verbosity);
    }

    public static function traverse($eg, $output, $indent, $verbosity) {

        foreach ($eg->getChildren() as $child) {
            if ($child instanceof \DSpec\ExampleGroup) {

                if (!$child->hasFailures()) {
                    continue;;
                }

                $output->writeln(str_repeat(" ", $indent) . $child->getTitle());
                static::traverse($child, $output, $indent + 2, $verbosity);
                continue;
            }

            if (!$child->isFailure()) {
                continue;
            }

            $output->writeln(sprintf(
                "%s<dspec-bold-fail>✖</dspec-bold-fail> <dspec-fail>%s</dspec-fail>",
                str_repeat(" ", $indent ),
                $child->getTitle()
            ));

            $e = $child->getFailureException();
            $msg = $verbosity ? (string) $e : $e->getMessage();

            foreach (explode("\n", $msg) as $line) {
                $output->writeln(str_repeat(" ", $indent + 2) . $line);
            }
        }
    }
}
