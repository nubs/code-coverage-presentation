<?php
namespace Chadicus;

/**
 * This class if for overriding global functions for use within unit tests.
 */
final class FunctionRegistry
{
    private static $functions = array();

    /**
     * Register a new function for testing.
     *
     * @param string $name The function name.
     * @param callable $function The callable to execute.
     *
     * @return void
     */
    public static function set($name, $function)
    {
        self::$functions[$name] = $function;
    }

    /**
     * Returns the custom function or the global function
     *
     * @param string $name The function name.
     *
     * @return callable
     */
    public static function get($name)
    {
        if (array_key_exists($name, self::$functions)) {
            return self::$functions[$name];
        }

        // return reference to global function
        return "\\{$name}";
    }

    /**
     * Sets all custom function properties to null.
     *
     * @return void
     */
    public static function reset()
    {
        self::$functions = array();
    }
}

/**
 * Custom override of \extension_loaded().
 *
 * @param string $name The extension name.
 *
 * @return boolean
 */
function extension_loaded($name)
{
    return call_user_func(FunctionRegistry::get('extension_loaded'), $name);
}
