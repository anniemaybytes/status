<?php

declare(strict_types=1);

namespace Status\Cache;

/**
 * Simple key store interface
 *
 * @package Status\Cache
 */
interface IKeyStore
{
    /**
     * Return value stored for key, or false if not existent
     *
     * @param string $key
     *
     * @return mixed
     */
    public function doGet(string $key);

    /**
     * @return array
     */
    public function getCacheHits(): array;

    /**
     * @return float
     */
    public function getExecutionTime(): float;

    /**
     * Set the key to value.
     * Return true on success or false on failure.
     *
     * @param string $key
     * @param mixed $value
     * @param int $time
     *
     * @return bool
     */
    public function doSet(string $key, $value, int $time = 10800): bool;

    /**
     * Delete the value stored against key.
     * Return true on success or false on failure.
     *
     * @param string $key
     *
     * @return bool
     */
    public function doDelete(string $key): bool;

    /**
     * @param string $key
     * @param int $n
     * @param int $initial
     * @param int $expiry
     *
     * @return int|false
     */
    public function doIncrement(string $key, int $n = 1, int $initial = 1, int $expiry = 0);

    /**
     * @param string $key
     * @param int $expiry
     *
     * @return bool
     */
    public function doTouch(string $key, int $expiry = 10800): bool;

    /**
     * @param bool $val
     */
    public function setClearOnGet(bool $val): void;

    public function doFlush(): void;

    /**
     * @return array
     */
    public function getAllKeys(): array;

    /**
     * @return array
     */
    public function getStats(): array;
}
