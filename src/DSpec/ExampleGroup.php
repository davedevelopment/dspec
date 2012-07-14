<?php

namespace DSpec;

use DSpec\Context\AbstractContext;

/**
 * This file is part of dspec
 *
 * Copyright (c) 2012 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class ExampleGroup extends Node 
{
    use Timeable;

    protected $parent;
    protected $context;
    protected $examples = array();
    protected $hooks = array(
        'beforeEach' => array(),
        'afterEach' => array(),
    );

    public function __construct($description, AbstractContext $context, ExampleGroup $parent = null)
    {
        $this->title = $description;
        $this->context = $context;
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Reporter $reporter)
    {
        $this->startTimer();
        $this->setErrorHandler();
        foreach ($this->examples as $example) {

            if ($example instanceof ExampleGroup) {
                $example->run($reporter);
                continue;
            }

            $example->startTimer();

            try {
                $context = clone $this->context;
                $this->runHooks('beforeEach', $context);
                $example->run($context);
                $this->runHooks('afterEach', $context, true);
                $reporter->examplePassed($example);
                $example->passed();

            } catch (Exception\PendingExampleException $e) {
                $example->pending($e->getMessage());
                $reporter->examplePending($example);
            } catch (Exception\SkippedExampleException $e) {
                $example->skipped($e->getMessage());
                $reporter->exampleSkipped($example);
            } catch (\Exception $e) {
                $example->failed($e);
                $reporter->exampleFailed($example);
            }

            $example->endTimer();
        }
        $this->restoreErrorHandler();
        $this->endTimer();
    }

    /**
     * Traverse ancestry running hooks
     *
     * @param string $name
     */
    public function runHooks($name, AbstractContext $context, $reverse = false)
    {
        $parent = $this->getParent();
        $hooks = $this->hooks[$name];

        if ($reverse) { 
            foreach (array_reverse($hooks) as $hook) {
                $hook->run($context); 
            }
            if ($parent) {
                $parent->runHooks($name, $context, $reverse);
            }
        } else {
            if ($parent) {
                $parent->runHooks($name, $context, $reverse);
            }
            foreach (array_reverse($hooks) as $hook) {
                $hook->run($context); 
            }
        } 
    }

    public function add($object)
    {
        if ($object instanceof Example) {
            return $this->addExample($object);
        }

        if ($object instanceof ExampleGroup) {
            return $this->addExampleGroup($object);
        }

        if ($object instanceof Hook) {
            return $this->addHook($object);
        }

        throw new \InvalidArgumentException("add currently only supports Examples, ExampleGroups and Hooks");
    }

    /**
     * Get total number of tests
     *
     * @return int
     */
    public function total()
    {
        $total = array_reduce($this->examples, function($x, $e) {
            $x += $e instanceof Example ? 1 : $e->total();
            return $x;
        }, 0);

        return $total;
    }

    public function addExample(Example $example)
    {
        $this->examples[] = $example;
    }

    public function addExampleGroup(ExampleGroup $exampleGroup)
    {
        $this->examples[] = $exampleGroup;
    }

    public function addHook(Hook $hook)
    {
        $this->hooks[$hook->getName()][] = $hook;
    }

    public function getChildren()
    {
        return $this->examples;
    }

    /**
     * @return array
     */
    public function getDescendants()
    {
        $descendants = array($this);

        foreach($this->examples as $e)
        {
            if ($e instanceof ExampleGroup) {
                $descendants = array_merge($descendants, $e->getDescendants());
            } else {
                $descendants[] = $e;
            }
        }

        return $descendants;
    }

    public function hasFailures()
    {
        foreach ($this->examples as $e) {
            if ($e instanceof Example) {
                if ($e->isFailure()) {
                    return true;
                }
            } else {
                if ($e->hasFailures()) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set error handler
     *
     */
    public function setErrorHandler()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }

    /**
     * Restore error handler
     *
     */
    public function restoreErrorHandler()
    {
        restore_error_handler();
    }
}
