<?php declare(strict_types=1);

namespace Status\Utilities;

use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteParser;

/**
 * Class View
 *
 * @package Status\Utilities
 */
class View
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
     * @param string $file
     *
     * @return string
     */
    public function assetUrl(string $file): string
    {
        return $this->config['site.site_root'] . $this->assets->path($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function cssUrl(string $file): string
    {
        return $this->assetUrl(sprintf('css/%s.css', $file));
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function jsUrl(string $file): string
    {
        return $this->assetUrl(sprintf('js/%s.js', $file));
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function imgUrl(string $file): string
    {
        return $this->assetUrl(sprintf('img/%s', $file));
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
     * @param $key
     *
     * @return mixed
     */
    public function config($key)
    {
        return $this->config[$key];
    }
}
