<?php

declare(strict_types=1);

namespace Status\Utilities;

/**
 * Class Assets
 *
 * @package Status\Utilities
 */
final class Assets
{
    /**
     * @var array
     * @Inject("config")
     */
    private $config;

    /**
     * @param string $filename
     *
     * @return string
     */
    public function path(string $filename): string
    {
        return $this->config['site.assets_root'] . '/' . $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function absolutePath($filename): string
    {
        return PUBLIC_ROOT . $this->config['site.assets_root'] . '/' . $filename;
    }
}
