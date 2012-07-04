<?php

namespace DSpec\Formatter;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

abstract class AbstractFormatter implements FormatterInterface, EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->output->getFormatter()->setStyle('dspec-fail', new OutputFormatterStyle('red'));
        $this->output->getFormatter()->setStyle('dspec-bold-fail', new OutputFormatterStyle('red', null, array('bold')));
        $this->output->getFormatter()->setStyle('dspec-pending', new OutputFormatterStyle('blue'));
        $this->output->getFormatter()->setStyle('dspec-skipped', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('dspec-pass', new OutputFormatterStyle('green'));
        $this->output->getFormatter()->setStyle('dspec-bold-pass', new OutputFormatterStyle('green', null, array('bold')));
        $this->output->getFormatter()->setStyle('dspec-meta', new OutputFormatterStyle('white', null, array()));
        return $this;
    }

    static public function getSubscribedEvents() 
    {
        return array();
    }
}
