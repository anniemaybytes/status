<?php
define('BASE_ROOT', __DIR__);
require_once BASE_ROOT . '/vendor/autoload.php'; // set up autoloading

$app = \myApp\Dispatcher::app();
$app->run();