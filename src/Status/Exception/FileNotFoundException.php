<?php declare(strict_types=1);

namespace Status\Exception;

/**
 * Class FileNotFoundException
 *
 * @package Status\Exception
 */
class FileNotFoundException extends NotFound
{
    /** @var string $what */
    protected $what;

    /**
     * FileNotFoundException constructor.
     *
     * @param string $what
     */
    public function __construct(string $what)
    {
        $this->what = $what;
        parent::__construct("File {$what} not found");
    }

    /**
     * @return string
     */
    public function getWhat() : string
    {
        return $this->what;
    }
}
