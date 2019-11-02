<?php

namespace myApp;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @var Utilities\View
     */
    private $view_functions;

    public function __construct(Utilities\View $view_functions)
    {
        $this->view_functions = $view_functions;
    }

    public function getName()
    {
        return 'slim';
    }

    public function getFunctions()
    {
        $fn = $this->view_functions;

        // map function names in twig to function names implemented in
        // the view functions utility
        $functionMappings = [
            'baseurl' => 'baseUrl',
            'currenturl' => 'currentUrl',
            'config' => 'config',
            'di' => 'getDi',
            'url' => 'pathFor',
            'asseturl' => 'assetUrl',
            'cssurl' => 'cssUrl',
            'jsurl' => 'jsUrl',
            'imgurl' => 'imgUrl',
            'qs' => 'getQueryString',
        ];

        $functions = [];
        foreach ($functionMappings as $nameFrom => $nameTo) {
            $callable = [$fn, $nameTo];
            if (!is_callable($callable)) throw new Exception("Function $nameTo does not exist in view functions");
            $functions[] = new TwigFunction($nameFrom, $callable);
        }

        return $functions;
    }
}
