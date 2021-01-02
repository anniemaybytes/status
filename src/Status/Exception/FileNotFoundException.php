<?php

declare(strict_types=1);

namespace Status\Exception;

use Exception;

/**
 * Class FileNotFoundException
 *
 * @package Status\Exception
 */
final class FileNotFoundException extends Exception
{
    protected string $what;

    public function __construct(string $what)
    {
        $this->what = $what;
        parent::__construct("File {$what} not found");
    }

    public function getWhat(): string
    {
        return $this->what;
    }
}
