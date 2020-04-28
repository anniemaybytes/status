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
    private $assets;

    /**
     * @Inject("config")
     * @var array
     */
    private $config;

    /**
     * @Inject
     * @var RouteParser
     */
    private $router;

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

    /**
     * @param string $name
     * @param array $data
     * @param array $queryParams
     *
     * @return string
     */
    public function pathFor(string $name, array $data = [], array $queryParams = []): string
    {
        return $this->router->relativeUrlFor($name, $data, $queryParams);
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function baseUrl(Request $request): string
    {
        $uri = $request->getUri();
        $uri = $uri->withUserInfo('');

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        return "$scheme://$authority";
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public function config($key)
    {
        return $this->config[$key];
    }
}
