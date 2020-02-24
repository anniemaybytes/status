<?php

declare(strict_types=1);

namespace Status;

use BadFunctionCallException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class TwigExtension
 *
 * @package Status
 */
final class TwigExtension extends AbstractExtension
{
    /**
     * @var Utilities\View
     */
    private $view_functions;

    /**
     * TwigExtension constructor.
     *
     * @param Utilities\View $view_functions
     */
    public function __construct(Utilities\View $view_functions)
    {
        $this->view_functions = $view_functions;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'status';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        $fn = $this->view_functions;

        // map function names in twig to function names implemented in
        // the view functions utility
        $functionMappings = [
            'baseurl' => 'baseUrl',
            'config' => 'config',
            'url' => 'pathFor',
            'asseturl' => 'assetUrl',
            'cssurl' => 'cssUrl',
            'jsurl' => 'jsUrl',
            'imgurl' => 'imgUrl',
        ];

        $functions = [];
        foreach ($functionMappings as $nameFrom => $nameTo) {
            $callable = [$fn, $nameTo];
            if (!is_callable($callable)) {
                throw new BadFunctionCallException("Function $nameTo does not exist in view functions");
            }
            $functions[] = new TwigFunction($nameFrom, $callable);
        }

        return $functions;
    }
}
