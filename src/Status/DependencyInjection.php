<?php

declare(strict_types=1);

namespace Status;

use ArrayAccess;
use DI;
use Exception;
use Psr\Container\ContainerInterface as Container;

/**
 * Class DependencyInjection
 *
 * @package Status
 */
final class DependencyInjection
{
    /** @throws Exception */
    public static function build(array $baseDefinitions, ArrayAccess $config, bool $enableCompilation): Container
    {
        // prepare builder
        $builder = new DI\ContainerBuilder();
        $builder->useAttributes(true);
        if ($enableCompilation) {
            $builder->enableCompilation(BASE_ROOT);
        }
        $builder->addDefinitions($baseDefinitions);

        // run build
        $di = $builder->build();

        // dynamic definitions
        $di->set('config', $config);
        $di->set('ob_level', ob_get_level());

        return $di;
    }
}
