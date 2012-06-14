<?php

namespace DSpec;

/**
 * Static class that simply acts as a front controller, passing things where 
 * they need to go
 */
class DSpec
{
    protected static $compiler;

    /**
     * @param string $description
     * @param Closure $closure
     */
    public static function describe($description, \Closure $closure)
    {
        static::getCompiler()->describe($description, $closure);
    }


    /**
     * @param string $context
     * @param Closure $closure
     */
    public static function context($context, \Closure $closure)
    {
        static::getCompiler()->context($context, $closure);
    }

    /**
     * @param string $example
     * @param Closure $closure
     */
    public static function it($example, \Closure $closure)
    {
        static::getCompiler()->it($example, $closure);
    }

    /**
     * @param \Closure $closure
     */
    public static function beforeEach(\Closure $closure)
    {
        static::getCompiler()->beforeEach($closure);
    }

    /**
     * @param \Closure $closure
     */
    public static function afterEach(\Closure $closure)
    {
        static::getCompiler()->afterEach($closure);
    }

    /**
     * @param string $message
     */
    public static function pending($message = "{no message}")
    {
        throw new Exception\PendingExampleException($message);
    }

    /**
     * @param string $message
     */
    public static function skip($message = "{no message}")
    {
        throw new Exception\SkippedExampleException($message);
    }

    /**
     * @return Compiler
     */
    public static function getCompiler()
    {
        if (static::$compiler) {
            return static::$compiler;
        }

        return static::$compiler = new Compiler();
    }

    /**
     * Set Compiler
     *
     * @param Compiler $compiler;
     */
    public static function setCompiler(Compiler $compiler)
    {
        static::$compiler = $compiler;
    }
}
