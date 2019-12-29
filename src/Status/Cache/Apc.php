<?php declare(strict_types=1);

namespace Status\Cache;

/**
 * Class Apc
 *
 * @package Status\Cache
 */
class Apc implements IKeyStore
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function fetch(string $key)
    {
        return apcu_fetch($key);
    }

    /**
     * @param string $key
     *
     * @return bool|string[]
     */
    public function exists(string $key)
    {
        return apcu_exists($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expiry
     *
     * @return array|bool
     */
    public function add(string $key, $value, int $expiry = 3600)
    {
        return apcu_add($key, $value, $expiry);
    }

    /**
     * @param string $key
     *
     * @return bool|string[]
     */
    public function delete(string $key)
    {
        return apcu_delete($key);
    }
}
