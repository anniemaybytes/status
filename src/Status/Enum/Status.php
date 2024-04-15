<?php

declare(strict_types=1);

namespace Status\Enum;

/**
 * Enum Status
 *
 * @package Status\Enum
 */
enum Status: int
{
    case DOWN = 0;
    case NORMAL = 1;
    case ISSUES = 2;
}
