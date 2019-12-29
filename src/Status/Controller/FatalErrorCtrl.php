<?php declare(strict_types=1);

namespace Status\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;
use Throwable;
use Tracy\Debugger;

/**
 * Class FatalErrorCtrl
 *
 * @package Status\Controller
 */
class FatalErrorCtrl
{
    /**
     * @var Container
     */
    private $di;

    /**
     * @var Twig
     */
    private $view;

    /**
     * FatalErrorCtrl constructor.
     *
     * @param $di
     */
    public function __construct(Container &$di)
    {
        $this->di = &$di;
        $this->view = $this->di['view'];
    }

    /**
     * Render very simple error page in case of fatal PHP error
     * More detailed code that may depend on DI wrapped inside try blocks, each their own so that failure of one will not cause
     * previous ones to lose data.
     *
     * @param Request $request
     * @param Response $response
     * @param Throwable $error
     *
     * @return ResponseInterface
     */
    public function handleError(Request $request, Response $response, Throwable $error): ResponseInterface
    {
        // have tracy log the error
        Debugger::log($error, Debugger::ERROR);

        // clear the body first
        $body = $response->getBody();
        $body->rewind();
        $response = $response->withBody($body);

        // clear output buffer
        while (ob_get_level() > @$this->di['obLevel']) {
            $status = ob_get_status();
            if (in_array($status['name'], ['ob_gzhandler', 'zlib output compression'], true)) {
                break;
            }
            if (!@ob_end_clean()) { // @ may be not removable
                break;
            }
        }

        return $this->view->render($response, 'error.twig', [])->withStatus(500);
    }
}
