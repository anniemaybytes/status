<?php

declare(strict_types=1);

namespace Status\CachedValue;

use InvalidArgumentException;

/**
 * Class IntParam
 *
 * @package Status\CachedValue
 */
abstract class IntParam extends Base
{
    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    protected static function validateParam(mixed $param): void
    {
        if (!is_int($param) && !ctype_digit((string)$param)) {
            throw new InvalidArgumentException(
                'Paremeter must be either integer or string consisting of numeric values'
            );
        }
    }
}
