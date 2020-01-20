<?php declare(strict_types=1);

namespace Status\Utilities;

use Psr\Http\Message\ServerRequestInterface;
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
     * @Inject
     * @var ServerRequestInterface
     */
    private $request;

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
     * @return string
     */
    public function baseUrl(): string
    {
        $uri = $this->request->getUri();
        $uri = $uri->withUserInfo('');

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        return "$scheme://$authority";
    }

    /**
     * @return string
     */
    public function currentUrl(): string
    {
        return $this->baseUrl() . $this->request->getUri()->getPath();
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

    /**
     * @param array $params
     * @param array $formParams
     *
     * @return string
     */
    public function getQueryString(array $params = [], array $formParams = []): string
    {
        $request = $this->request;
        $getParams = $request->getQueryParams();
        $getParams = array_merge($getParams, $formParams);
        $getParams = array_merge($getParams, $params);
        $getParams = array_filter(
            $getParams,
            function ($e) {
                return $e !== '';
            }
        );
        return http_build_query($getParams);
    }
}
