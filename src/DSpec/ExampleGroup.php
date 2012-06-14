<?php

namespace DSpec;

class ExampleGroup extends Node 
{
    protected $closure;
    protected $parent;

    protected $examples = array();
    protected $hooks = array(
        'beforeEach' => array(),
        'afterEach' => array(),
    );

    public function __construct($description, \Closure $closure, ExampleGroup $parent = null)
    {
        $this->title = $description;
        $this->closure = $closure;
        $this->parent = $parent;
    }

    /**
     * {@inheritDoc}
     */
    public function run(Reporter $reporter)
    {
        foreach ($this->examples as $example) {

            if ($example instanceof ExampleGroup) {
                $example->run($reporter);
                continue;
            }

            try {
                $this->runHooks('beforeEach', $reporter);
                $example->run($reporter);
                $this->runHooks('afterEach', $reporter);
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
        }

    }

    /**
     * Traverse ancestry running hooks
     *
     * @param string $name
     * @param Reporter $reporter
     */
    public function runHooks($name, Reporter $reporter)
    {
        $parent = $this->getParent();

        if ($parent) {
            $parent->runHooks($name, $reporter);
        }
        
        foreach ($this->hooks[$name] as $hook) {
            $hook->run($reporter); 
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

    /**
     * @return \Closure
     */
    public function getClosure()
    {
        return $this->closure;
    }
}
