<?php

declare(strict_types=1);

namespace Status\Environment;

use Status\Cache\Apc;
use Status\Cache\IKeyStore;

use function DI\autowire;

/**
 * Class CLI
 *
 * @package Status\Environment
 */
final class CLI
{
    public static function definitions(): array
    {
        return [
            IKeyStore::class => autowire(Apc::class)
        ];
    }
}
