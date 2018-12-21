<?php
namespace myApp\Utilities;

class Assets
{
  private $di;

  public function __construct(\Slim\Container $di)
  {
    $this->di = $di;
  }

  public function path($filename)
  {
    return $this->di['config']['site.assets_root'] . '/' . $filename;
  }

  public function absolutePath($filename)
  {
    return PUBLIC_ROOT . $this->di['config']['site.assets_root'] . '/' . $filename;
  }
}
