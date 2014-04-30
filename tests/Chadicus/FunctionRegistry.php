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
 * @return boolean
 */
function extension_loaded()
{
    return call_user_func_array(FunctionRegistry::get('extension_loaded'), func_get_args());
}

/**
 * Custom override of \curl_init().
 *
 * @return mixed
 */
function curl_init()
{
    return call_user_func_array(FunctionRegistry::get('curl_init'), func_get_args());
}

/**
 * Custom override of \curl_setopt_array().
 *
 * @return boolean
 */
function curl_setopt_array()
{
    return call_user_func_array(FunctionRegistry::get('curl_setopt_array'), func_get_args());
}

/**
 * Custom override of \curl_exec().
 *
 * @return string
 */
function curl_exec()
{
    return call_user_func_array(FunctionRegistry::get('curl_exec'), func_get_args());
}

/**
 * Custom override of \curl_error().
 *
 * @return string
 */
function curl_error()
{
    return call_user_func_array(FunctionRegistry::get('curl_error'), func_get_args());
}

/**
 * Custom override of \curl_getinfo().
 *
 * @return string
 */
function curl_getinfo()
{
    return call_user_func_array(FunctionRegistry::get('curl_getinfo'), func_get_args());
}
