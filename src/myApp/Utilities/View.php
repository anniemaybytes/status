<?php

namespace myApp\Utilities;

class View
{
    private $di;

    public function __construct(\Slim\Container $di)
    {
        $this->di = $di;
    }

    public function assetUrl($file)
    {
        return $this->di['config']['site.site_root'] . $this->di['utility.assets']->path($file);
    }

    public function cssUrl($file)
    {
        return $this->assetUrl(sprintf('css/%s.css', $file));
    }

    public function jsUrl($file)
    {
        return $this->assetUrl(sprintf('js/%s.js', $file));
    }

    public function imgUrl($file)
    {
        return $this->assetUrl(sprintf('img/%s', $file));
    }

    public function pathFor($name, $data = [], $queryParams = [])
    {
        return $this->di['router']->pathFor($name, $data, $queryParams);
    }

    public function baseUrl()
    {
        /**
         * @var \Slim\Http\Uri $uri
         */
        $uri = $this->di['request']->getUri();
        $uri = $uri->withUserInfo('');

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        return "$scheme://$authority";
    }

    public function currentUrl()
    {
        return $this->baseUrl() . $this->di['request']->getUri()->getPath();
    }

    public function config($key)
    {
        return $this->di['config'][$key];
    }

    public function getDi()
    {
        return $this->di;
    }

    public function getQueryString($params = array(), $formParams = array())
    {
        $request = $this->di['request'];
        $getParams = $request->getQueryParams();
        $getParams = array_merge($getParams, $formParams);
        $getParams = array_merge($getParams, $params);
        $getParams = array_filter($getParams, function($e) { return $e !== ''; });
        $qs = http_build_query($getParams);
        return $qs;
    }
}
