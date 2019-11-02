<?php

define('BASE_ROOT', __DIR__);
require_once BASE_ROOT . '/vendor/autoload.php'; // set up autoloading

use myApp\Dispatcher;

$app = Dispatcher::app();
$app->run();
