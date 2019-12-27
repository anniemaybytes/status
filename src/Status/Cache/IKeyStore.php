<?php

namespace Status\Cache;

/**
 * Simple key store interface
 *
 */
interface IKeyStore
{
    /**
     * Return value stored for key, or false if not existent
     *
     * @param string $key
     */
    public function fetch($key);

    /**
     * Set the key to value.
     * Return true on success or false on failure.
     *
     * @param $key
     * @param $value
     * @param int|number $time
     */
    public function add($key, $value, $time = 3600);

    /**
     * @param $key
     *
     * @return mixed
     */
    public function exists($key);

    /**
     * Delete the value stored against key.
     * Return true on success or false on failure.
     *
     * @param $key
     */
    public function delete($key);
}
