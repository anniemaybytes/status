<?php

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
    public function fetch($key)
    {
        return apcu_fetch($key);
    }

    /**
     * @param $key
     *
     * @return bool|string[]
     */
    public function exists($key)
    {
        return apcu_exists($key);
    }

    /**
     * @param $key
     * @param $value
     * @param int $expiry
     *
     * @return array|bool
     */
    public function add($key, $value, $expiry = 3600)
    {
        return apcu_add($key, $value, $expiry);
    }

    /**
     * @param $key
     *
     * @return bool|string[]
     */
    public function delete($key)
    {
        return apcu_delete($key);
    }
}
