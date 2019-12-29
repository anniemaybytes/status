<?php declare(strict_types=1);

namespace Status\Utilities;

use Slim\Container;

/**
 * Class Assets
 *
 * @package Status\Utilities
 */
class Assets
{
    private $di;

    /**
     * Assets constructor.
     *
     * @param Container $di
     */
    public function __construct(Container $di)
    {
        $this->di = $di;
    }

    /**
     * @param $filename
     *
     * @return string
     */
    public function path(string $filename) : string
    {
        return $this->di['config']['site.assets_root'] . '/' . $filename;
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    public function absolutePath($filename) : string
    {
        return PUBLIC_ROOT . $this->di['config']['site.assets_root'] . '/' . $filename;
    }
}
