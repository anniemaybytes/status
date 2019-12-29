<?php declare(strict_types=1);

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
    public function fetch(string $key);

    /**
     * Set the key to value.
     * Return true on success or false on failure.
     *
     * @param string $key
     * @param $value
     * @param int $time
     */
    public function add(string $key, $value, int $time = 3600);

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function exists(string $key);

    /**
     * Delete the value stored against key.
     * Return true on success or false on failure.
     *
     * @param string $key
     */
    public function delete(string $key);
}
