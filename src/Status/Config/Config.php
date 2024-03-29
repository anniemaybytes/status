<?php

declare(strict_types=1);

namespace Status\Config;

use ArrayAccess;
use ClosedGeneratorException;
use RuntimeException;

/**
 * Class Config
 *
 * @package Status\Config
 */
final class Config implements ArrayAccess
{
    private const FILES = [
        BASE_ROOT . '/config/private.ini'
    ];

    private array $config;

    public function __construct(array $files = [])
    {
        if (!count($files)) {
            $files = self::FILES;
        }

        $mergedArray = Defaults::CONFIG;
        foreach ($files as $file) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $mergedArray = array_merge($mergedArray, Loader::load($file));
        }
        $this->config = Loader::parse($mergedArray);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->config[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!isset($this->config[$offset])) {
            throw new RuntimeException("$offset is not a valid configuration property");
        }
        if ($this->config[$offset] === UndefinedValue::class) {
            throw new RuntimeException("Config property $offset is uninitialized");
        }

        return $this->config[$offset];
    }

    /** @throws ClosedGeneratorException */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ClosedGeneratorException('Unable to modify configuration during runtime');
    }

    /** @throws ClosedGeneratorException */
    public function offsetUnset(mixed $offset): void
    {
        throw new ClosedGeneratorException('Unable to modify configuration during runtime');
    }
}
