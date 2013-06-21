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
use NyanCat\Cat;
use NyanCat\Rainbow;
use NyanCat\Team;
use NyanCat\Scoreboard;
use Fab\Factory as Fab;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class NyanCat extends AbstractFormatter implements FormatterInterface
{
    public function __construct()
    {
        if (!class_exists("NyanCat\Scoreboard")) {
            throw new \InvalidArgumentException("The NyanCat formatter requires the whatthejeff/nyancat-scoreboard library");
        }
        
        $this->scoreboard = new Scoreboard(
            new Cat(),
            new Rainbow(
                Fab::getFab(
                    empty($_SERVER['TERM']) ? 'unknown' : $_SERVER['TERM']
                ),
                array('-', '_'),
                74
            ),
            array(
                new Team('pass', 'green', '.'),
                new Team('fail', 'red', 'x'),
                new Team('pend', 'blue', '-'),
                new Team('skip', 'yellow', '-'),
            ),
            6,
            array($this, 'write')
        );
    }

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
        for ($i = 0; $i < 5; $i++) {
            $this->output->writeln("");
        }

        $summary = new Summary();
        $summary->setOutput($this->output);
        $summary->format($r, $suite, $verbose);
        $failureTree = new FailureTree();
        $failureTree->setOutput($this->output);
        $failureTree->format($r, $suite, $verbose);
    }

    public function write($text)
    {
        return $this->output->write($text);
    }

    public function onExampleFail(ExampleFailEvent $e)
    {
        $this->scoreboard->score('fail');
    }

    public function onExamplePass(ExamplePassEvent $e)
    {
        $this->scoreboard->score('pass');
    }

    public function onExamplePend(ExamplePendEvent $e)
    {
        $this->scoreboard->score('pend');
    }

    public function onExampleSkip(ExampleSkipEvent $e)
    {
        $this->scoreboard->score('skip');
    }
}
