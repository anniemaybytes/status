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
     */
    public function doGet(string $key): mixed;

    public function getCacheHits(): array;

    public function getExecutionTime(): float;

    public function doSet(string $key, mixed $value, int $time = 10800): bool;

    public function doDelete(string $key): bool;

    public function doIncrement(string $key, int $n = 1, int $initial = 1, int $expiry = 0): bool|int;

    public function doTouch(string $key, int $expiry = 10800): bool;

    public function setClearOnGet(bool $val): void;

    public function doFlush(): void;

    public function getAllKeys(): array;

    public function getStats(): array;
}
