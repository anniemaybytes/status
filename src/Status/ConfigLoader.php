<?php

namespace Status;

use Exception;

/**
 * Class ConfigLoader
 *
 * @package Status
 */
class ConfigLoader
{
    /**
     * @param $array
     * @param $prefix
     * @param bool $deep
     *
     * @return array
     */
    private static function parseArray($array, $prefix, $deep = false)
    {
        $output = [];

        if ($prefix !== '') {
            $prefix .= '.';
        }

        foreach ($array as $k => $v) {
            if (is_array($v) && !isset($v[0]) && !$deep) {
                // if it's a subarray, and it *looks* associative and is not deep
                $output = array_merge($output, self::parseArray($v, $prefix . $k, true));
            } else {
                $output[$prefix . $k] = $v;
            }
        }
        return $output;
    }

    /**
     * @param $path
     *
     * @return array
     * @throws Exception
     */
    private static function loadFile($path)
    {
        // load it as an ini
        if (!file_exists($path)) {
            throw new Exception("Couldn't find config file $path");
        }
        $parsedFile = parse_ini_file($path, true);

        return self::parseArray($parsedFile, '');
    }

    /**
     * @param string $configPath
     *
     * @return array
     * @throws Exception
     */
    public static function load($configPath = 'config/')
    {
        if ($configPath[0] !== '/' && strpos($configPath, '://') === false) {
            $configPath = BASE_ROOT . '/' . $configPath;
        }
        return self::loadFile($configPath . 'config.ini');
    }
}
