<?php

namespace Status\Exception;

/**
 * Class FileNotFoundException
 *
 * @package Status\Exception
 */
class FileNotFoundException extends NotFound
{
    protected $what;

    /**
     * FileNotFoundException constructor.
     *
     * @param $what
     */
    public function __construct($what)
    {
        $this->what = $what;
        parent::__construct("File {$what} not found");
    }

    /**
     * @return mixed
     */
    public function getWhat()
    {
        return $this->what;
    }
}
