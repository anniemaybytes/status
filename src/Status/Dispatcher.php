<?php declare(strict_types=1);

namespace Status;

use Exception;
use PetrKnap\Php\Singleton\SingletonInterface;
use PetrKnap\Php\Singleton\SingletonTrait;
use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\ResponseFactoryInterface;
use RuntimeException;
use RunTracy\Helpers\Profiler\Profiler;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteParser;
use Status\Route as R;

/**
 * Class Dispatcher
 *
 * @package Status
 */
final class Dispatcher implements SingletonInterface
{
    use SingletonTrait;
    /**
     * @var App $app
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Container
     */
    private $di;

    /**
     * Returns the slim application object
     *
     * @return App
     */
    public static function app(): App
    {
        return self::getInstance()->app;
    }

    /**
     * @param mixed $key
     *
     * @return mixed
     */
    public static function config($key)
    {
        return self::getInstance()->config[$key];
    }

    /**
     * @return array
     */
    public static function getConfig(): array
    {
        return self::getInstance()->config;
    }

    /**
     * Returns the container object
     *
     * @return Container
     */
    public static function di(): Container
    {
        return self::getInstance()->di;
    }

    private function initConfig(): void
    {
        Profiler::start('initConfig');
        $config = ConfigLoader::load();
        Profiler::finish('initConfig');

        $allowedModes = ['production', 'development'];
        if (!in_array($config['mode'], $allowedModes, true)) {
            throw new RuntimeException(
                'Can not start application with non-recognized mode: ' . $config['mode'] . '. Must be one of: ' . implode(
                    ', ',
                    $allowedModes
                )
            );
        }

        $config['templates.path'] = BASE_ROOT . '/' . $config['templates.path'];
        $config['logs_dir'] = BASE_ROOT . '/' . $config['logs_dir'];
        $this->config = $config;
    }

    /**
     * @throws Exception
     */
    private function initDependencyInjection(): void
    {
        Profiler::start('initDependencyInjection');
        $this->di = DependencyInjection::setup($this->config);
        Profiler::finish('initDependencyInjection');
    }

    private function initApplication(): void
    {
        AppFactory::setContainer($this->di);
        $app = AppFactory::create();

        Profiler::start('initRoutes');

        $routeCollector = $app->getRouteCollector();
        $this->di->set(ResponseFactoryInterface::class, $app->getResponseFactory());

        $routes = [
            new R\Main($app),
        ];
        $this->di->set('routes', $routes);
        $this->di->set(RouteParser::class, $routeCollector->getRouteParser());

        if ($this->di->get('config')['mode'] === 'production') {
            $routeCollector->setCacheFile(BASE_ROOT . '/routes.cache.php');
        }

        Profiler::finish('initRoutes');

        $this->app = $app;
    }

    /**
     * Dispatcher constructor.
     *
     * @throws Exception
     */
    protected function __construct()
    {
        $this->initConfig();
        $this->initDependencyInjection();
        $this->initApplication();
    }
}
