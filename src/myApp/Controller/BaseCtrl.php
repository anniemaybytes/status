<?php

namespace myApp\Controller;

abstract class BaseCtrl
{
  protected $di;

  /**
   * @var \Cache
   */
  protected $cache;

  /**
   * @var \Slim\Views\Twig
   */
  protected $view;

  /**
   * @var \myApp\Utilities\View
   */
  protected $view_functions;

  /**
   * The configuration array
   */
  protected $config;

  /**
   * @var \Slim\Http\Environment
   */
  protected $environment;

  public function setDependencies($di)
  {
    $this->config = $di['config'];
    $this->view_functions = $di['utility.view'];
    $this->environment = $di['environment'];
    $this->view = $di['view'];
    $this->cache = $di['cache'];
  }

  public function __construct($di)
  {
    $this->di = $di;

    $this->setDependencies($di);
  }
}
