<?php

namespace Status;

use BadFunctionCallException;

/**
 * Class Singleton
 *
 * @package Status
 */
class Singleton
{
    private static $instances = [];

    /**
     * Singleton constructor.
     *
     * @param $args
     */
    protected function __construct($args)
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new BadFunctionCallException("Cannot unserialize singleton");
    }

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static(func_get_args());
        }
        return self::$instances[$cls];
    }
}
