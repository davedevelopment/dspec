<?php

namespace DSpec\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class DSpecApplication extends Application
{
    protected $name = 'dspec';

    /**
     * @param string $version
     */
    public function __construct($version = 'dev')
    {
        parent::__construct('dspec', $version);
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message.'),
            new InputOption('--verbose', '-v', InputOption::VALUE_NONE, 'Increase verbosity of messages.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version.'),
            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output.'),
            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output.'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question.'),
        ));
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        return array(new Command\DSpecCommand());
    }

    /**
     * Gets the command name, short circuit
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'dspec';
    }
}
