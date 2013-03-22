<?php

namespace DSpec\Console;

use DSpec\Container;
use DSpec\ServiceProviderInterface;
use DSpec\Provider\ConfigServiceProvider;
use DSpec\Console\Command\DSpecCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\HelpCommand;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class DSpecApplication extends Application
{
    protected $container;
    protected $registeredProviders = array();
    protected $name = 'dspec';

    /**
     * @param string $version
     */
    public function __construct(Container $container, $version = 'dev')
    {
        $this->container = $container;
        parent::__construct('dspec', $version);
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // introspect input without definition
        $config = $input->getParameterOption(array('--config', '-c'));
        $verbose = $input->getParameterOption(array('--verbose', '-v'));
        $bootstrap = $input->getParameterOption(array('--bootstrap', '-b'));
        $profile = $input->getParameterOption(array('--profile', '-p'));

        if (true == $verbose) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        }

        $container = $this->getContainer();

        // event dispatcher 
        $container['dispatcher'] = new EventDispatcher;
        $container['input']  = $input;
        $container['output'] = $output;

        // config

        /**
         * Process options
         */
        $this->register(new ConfigServiceProvider(), array(
            'config.path' => $config,
        ));

        if ($profile) {
            $container['config.profile'] = $profile;
        }
        $overrides = array();
        if ($input->hasParameterOption(array('--verbose', '-v'))) {
            $overrides['verbose'] = true;
        }
        if ($bootstrap) {
            $overrides['bootstrap'] = $bootstrap;
        }
        $container['config.overrides'] = $overrides;

        /**
         * Should overwrite the profile with command line options here
         */
        $profile = $container['profile'];
        if ($profile->bootstrap) { 
            $inc = function() use ($profile) {
                require $profile->bootstrap;
            };
            $inc();
        }

        // extensions
        foreach($profile->extensions as $name => $options) {
            $class = "\\DSpec\\Provider\\" . ucfirst($name) . "ServiceProvider";
            if (!class_exists($class)) {
                $class = $name;
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException("class:$class not found");
                }
            } 
            $this->register(new $class, (array) $options);
        }

        $container['dspec.command'] = new DSpecCommand($container);

        $this->bootProviders();

        $this->add($container['dspec.command']);

        return parent::doRun($input, $output);
    }

    public function register($provider, array $values = array())
    {
        if (!($provider instanceof ServiceProviderInterface)) {
            throw new \InvalidArgumentException(
                get_class($provider) . ' should implement DSpec\\ServiceProviderInterface'
            );
        }

        $provider->register($this->getContainer());

        $container = $this->getContainer();
        foreach ($values as $key => $value) {
            $container[$key] = $value;
        }

        $this->registeredProviders[] = $provider;
    }

    public function bootProviders()
    {
        foreach ($this->registeredProviders as $provider)
        {
            $provider->boot($this, $this->getContainer());
        }
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
            new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Path to a configuration file'),
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Chosen configuration profile'),
            new InputOption('--bootstrap', '-b', InputOption::VALUE_REQUIRED, 'A php bootstrap file'),
        ));
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return array An array of default Command instances
     */
    protected function getDefaultCommands()
    {
        return array(new HelpCommand());
    }

    /**
     * Gets the command name, short circuit
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'dspec';
    }
}
