<?php

declare(strict_types=1);

namespace Status\Utilities;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteParser;
use Status\Exception\FileNotFoundException;

/**
 * Class View
 *
 * @package Status\Utilities
 */
final class View
{
    /**
     * @Inject
     * @var Assets
     */
    private Assets $assets;

    /**
     * @Inject("config")
     * @var array
     */
    private array $config;

    /**
     * @Inject
     * @var RouteParser
     */
    private RouteParser $router;

    /**
     * @param string $filename
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function assetUrl(string $filename): string
    {
        return $this->assets->path($filename);
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

    public function config(mixed $key): mixed
    {
        return $this->config[$key];
    }
}
