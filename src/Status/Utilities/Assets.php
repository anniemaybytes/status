<?php

declare(strict_types=1);

namespace Status\Utilities;

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

    /**
     * @param array $config
     *
     * @throws FileNotFoundException
     */
    public function __construct(array $config)
    {
        $this->publicPath = $config['static.location'];
        $this->manifestFile = BASE_ROOT . '/public/static/manifest.json';
        $this->loadCompiledAssets();
    }

    /**
     * @throws FileNotFoundException
     */
    private function loadCompiledAssets(): void
    {
        if (!file_exists($this->manifestFile)) {
            throw new FileNotFoundException($this->manifestFile);
        }

        /** @noinspection JsonEncodingApiUsageInspection */
        $this->compiledAssets = json_decode(file_get_contents($this->manifestFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to parse manifest file');
        }
    }

    /**
     * @param string $filename
     *
     * @return string
     * @throws FileNotFoundException
     */
    public function path(string $filename): string
    {
        if (array_key_exists($filename, $this->compiledAssets)) {
            return $this->publicPath . $this->compiledAssets[$filename];
        }

        throw new FileNotFoundException($filename);
    }
}
