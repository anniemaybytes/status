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
    protected static function validateParam(mixed $param): bool
    {
        if (!is_int($param) && !ctype_digit((string)$param)) {
            throw new InvalidArgumentException(
                'Paremeter given to IntParam CachedValue must be either integer or string consisting of numeric values'
            );
        }
        return true;
    }
}
