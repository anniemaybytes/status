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
        $mappings = [
            'config' => 'config',
            'path' => 'pathFor',
            'base_url' => 'baseUrl',
            'asset' => 'assetPath',
        ];

        $functions = [];
        foreach ($mappings as $virtual => $method) {
            $callable = [$this->viewFunctions, $method];
            if (!is_callable($callable)) { // @phpstan-ignore function.alreadyNarrowedType
                throw new BadFunctionCallException(
                    "Function $method does not exist in " . get_class($this->viewFunctions)
                );
            }
            $functions[] = new TwigFunction($virtual, $callable);
        }

        return $functions;
    }
}
