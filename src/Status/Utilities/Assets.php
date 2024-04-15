<?php

declare(strict_types=1);

namespace Status\Utilities;

use ArrayAccess;
use JsonException;
use RuntimeException;
use Status\Exception\FileNotFoundException;

/**
 * Class Assets
 *
 * @package Status\Utilities
 */
final class Assets
{
    private string $publicPath;

    private string $manifestFile;
    private array $compiledAssets = [];

    public function __construct(ArrayAccess $config)
    {
        $this->publicPath = $config['static.location'];
        $this->manifestFile = BASE_ROOT . '/public/static/manifest.json';
        $this->loadCompiledAssets();
    }

    private function loadCompiledAssets(): void
    {
        if (!file_exists($this->manifestFile)) {
            throw new RuntimeException('Unable to locate manifest file');
        }

        try {
            $this->compiledAssets = json_decode(
                file_get_contents($this->manifestFile),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException('Failed to parse manifest file', 0, $e);
        }
    }

    /** @throws FileNotFoundException */
    public function path(string $filename): string
    {
        if (array_key_exists($filename, $this->compiledAssets)) {
            return $this->publicPath . $this->compiledAssets[$filename];
        }

        throw new FileNotFoundException($filename);
    }
}
