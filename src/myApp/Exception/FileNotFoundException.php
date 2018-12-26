<?php

namespace myApp\Exception;

class FileNotFoundException extends NotFound
{
    protected $what;

    public function __construct($what)
    {
        $this->what = $what;
        parent::__construct("File {$what} not found");
    }

    public function getWhat()
    {
        return $this->what;
    }
}
