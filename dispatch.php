<?php

define('BASE_ROOT', __DIR__);
define('ERROR_REPORTING', E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING | E_DEPRECATED));
require_once BASE_ROOT . '/vendor/autoload.php'; // set up autoloading

use RunTracy\Helpers\Profiler\Profiler;
use RunTracy\Middlewares\TracyMiddleware;
use Status\Dispatcher;
use Tracy\Debugger;

date_default_timezone_set('UTC');
error_reporting(ERROR_REPORTING);

// enable profiler for basic startup
Profiler::enable();
Profiler::start('app');
Profiler::start('initApp');
$app = Dispatcher::app();
Profiler::finish('initApp');

$di = $app->getContainer();

// disable further profiling based on run mode
if ($di['config']['mode'] == 'production') {
    Profiler::disable();
}

Debugger::enable($di['config']['mode'] == 'development' ? Debugger::DEVELOPMENT : Debugger::PRODUCTION, BASE_ROOT . '/logs');
if ($di['config']['mode'] == 'production') { // tracy resets error_reporting to E_ALL when it's enabled, silence it on production please
    error_reporting(ERROR_REPORTING);
}
Debugger::$maxDepth = 5;
Debugger::$maxLength = 520;
Debugger::$logSeverity = ERROR_REPORTING;
Debugger::$reservedMemorySize = 5000000; // 5 megabytes because we increase depth for bluescreen
Debugger::getBlueScreen()->maxDepth = 7;
Debugger::getBlueScreen()->maxLength = 520;

Profiler::start('initMiddlewares');
if ($di['config']['mode'] == 'development') {
    $app->add(new TracyMiddleware($app));
}
Profiler::finish('initMiddlewares');

$app->run();
Profiler::enable(); // enable back profiler to finish() on what it started before it might've been disabled
Profiler::finish('app');
