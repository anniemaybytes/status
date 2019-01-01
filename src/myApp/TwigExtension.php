<?php
namespace myApp;

use Exception;
use Twig_Extension;
use Twig_SimpleFunction;

class TwigExtension extends Twig_Extension
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
        $functionMappings = array(
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
        );

        $functions = array();
        foreach ($functionMappings as $nameFrom => $nameTo)
        {
            $callable = array($fn, $nameTo);
            if (!is_callable($callable)) throw new Exception("Function $nameTo does not exist in view functions");
            $functions[] = new Twig_SimpleFunction($nameFrom, $callable);
        }

        return $functions;
    }
}
