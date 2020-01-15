<?php declare(strict_types=1);

namespace Status;

use BadMethodCallException;

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
     * @param array $args
     */
    protected function __construct(array $args)
    {
    }

    protected function __clone()
    {
    }

    public function __wakeup()
    {
        throw new BadMethodCallException("Cannot unserialize singleton");
    }

    /**
     * @return object
     */
    public static function getInstance(): object
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static(func_get_args());
        }
        return self::$instances[$cls];
    }
}
