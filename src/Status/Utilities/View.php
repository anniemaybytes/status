<?php declare(strict_types=1);

namespace Status\Utilities;

use DI\Container;
use Slim\Http\Uri;

/**
 * Class View
 *
 * @package Status\Utilities
 */
class View
{
    /**
     * @var Container $di
     */
    private $di;

    /**
     * View constructor.
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $file
     *
     * @return string
     */
    public function assetUrl(string $file): string
    {
        return $this->di->get('config')['site.site_root'] . $this->di->get('utility.assets')->path($file);
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
        return $this->di->get('router')->relativeUrlFor($name, $data, $queryParams);
    }

    /**
     * @return string
     */
    public function baseUrl(): string
    {
        /**
         * @var Uri $uri
         */
        $uri = $this->di->get('request')->getUri();
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
        return $this->baseUrl() . $this->di->get('request')->getUri()->getPath();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function config($key)
    {
        return $this->di->get('config')[$key];
    }

    /**
     * @return Container
     */
    public function getDi(): Container
    {
        return $this->di;
    }

    /**
     * @param array $params
     * @param array $formParams
     *
     * @return string
     */
    public function getQueryString(array $params = [], array $formParams = []): string
    {
        $request = $this->di->get('request');
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
