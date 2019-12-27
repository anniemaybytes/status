<?php

namespace Status\Utilities;

use Slim\Container;
use Slim\Http\Uri;

/**
 * Class View
 *
 * @package Status\Utilities
 */
class View
{
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
     * @param $file
     *
     * @return string
     */
    public function assetUrl($file)
    {
        return $this->di['config']['site.site_root'] . $this->di['utility.assets']->path($file);
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function cssUrl($file)
    {
        return $this->assetUrl(sprintf('css/%s.css', $file));
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function jsUrl($file)
    {
        return $this->assetUrl(sprintf('js/%s.js', $file));
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function imgUrl($file)
    {
        return $this->assetUrl(sprintf('img/%s', $file));
    }

    /**
     * @param $name
     * @param array $data
     * @param array $queryParams
     *
     * @return mixed
     */
    public function pathFor($name, $data = [], $queryParams = [])
    {
        return $this->di['router']->pathFor($name, $data, $queryParams);
    }

    /**
     * @return string
     */
    public function baseUrl()
    {
        /**
         * @var Uri $uri
         */
        $uri = $this->di['request']->getUri();
        $uri = $uri->withUserInfo('');

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        return "$scheme://$authority";
    }

    /**
     * @return string
     */
    public function currentUrl()
    {
        return $this->baseUrl() . $this->di['request']->getUri()->getPath();
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function config($key)
    {
        return $this->di['config'][$key];
    }

    /**
     * @return Container
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @param array $params
     * @param array $formParams
     *
     * @return string
     */
    public function getQueryString($params = [], $formParams = [])
    {
        $request = $this->di['request'];
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
