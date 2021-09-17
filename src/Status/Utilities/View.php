<?php

declare(strict_types=1);

namespace Status\Utilities;

use ArrayAccess;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use Slim\Routing\RouteParser;
use Status\Exception\FileNotFoundException;

/**
 * Class View
 *
 * @package Status\Utilities
 */
final class View
{
    /** @Inject */
    private Assets $assets;

    /** @Inject("config") */
    private ArrayAccess $config;

    /** @Inject */
    private RouteParser $router;

    public function config(mixed $key): mixed
    {
        return $this->config[$key];
    }

    public function pathFor(string $name, array $data = [], array $queryParams = []): string
    {
        return $this->router->relativeUrlFor($name, $data, $queryParams);
    }

    public function baseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $uri = $uri->withUserInfo('');

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        return "$scheme://$authority";
    }

    public function assetPath(string $filename): string
    {
        try {
            return $this->assets->path($filename);
        } catch (FileNotFoundException $e) {
            throw new RuntimeException('Unable to construct path for given asset', 0, $e);
        }
    }
}
