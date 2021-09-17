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
    private Utilities\View $viewFunctions;

    public function __construct(Utilities\View $viewFunctions)
    {
        $this->viewFunctions = $viewFunctions;
    }

    public function getName(): string
    {
        return 'slim';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        $fn = $this->viewFunctions;

        // map function names in twig to function names implemented in
        // the view functions utility
        $functionMappings = [
            'config' => 'config',
            'path' => 'pathFor',
            'base_url' => 'baseUrl',
            'asset' => 'assetPath',
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
