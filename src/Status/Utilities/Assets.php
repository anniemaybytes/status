<?php

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
    public function path($filename)
    {
        return $this->di['config']['site.assets_root'] . '/' . $filename;
    }

    /**
     * @param $filename
     *
     * @return string
     */
    public function absolutePath($filename)
    {
        return PUBLIC_ROOT . $this->di['config']['site.assets_root'] . '/' . $filename;
    }
}
