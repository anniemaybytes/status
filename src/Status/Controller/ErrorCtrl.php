<?php declare(strict_types=1);

namespace Status\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Status\Exception\AccessDeniedException;
use Status\Exception\NotFound;
use Throwable;
use Tracy\Debugger;

/**
 * Class ErrorCtrl
 *
 * @package Status\Controller
 */
class ErrorCtrl extends BaseCtrl
{
    /**
     * @param int $statusCode
     *
     * @return array
     */
    private function getData(int $statusCode) : array
    {
        $data = [];
        $data['status_code'] = $statusCode;

        return $data;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param Throwable $exception
     *
     * @return Response
     */
    public function handleException(Request $request, Response $response, Throwable $exception) : Response
    {
        try {
            $statusCode = 500;
            if ($exception instanceof NotFound) {
                $statusCode = 404;
            } elseif ($exception instanceof AccessDeniedException) {
                $statusCode = 403;
            }

            $data = $this->getData($statusCode);

            $this->logError($request, $exception, $data);

            // clear the body first
            $body = $response->getBody();
            $body->rewind();
            $response = $response->withBody($body);

            // clear output buffer
            while (ob_get_level() > $this->di->get('obLevel')) {
                $status = ob_get_status();
                if (in_array($status['name'], ['ob_gzhandler', 'zlib output compression'], true)) {
                    break;
                }
                if (!@ob_end_clean()) { // @ may be not removable
                    break;
                }
            }

            switch ($statusCode) {
                case 404:
                    return $this->view->render($response, 'not_found.twig', $data)->withStatus($statusCode);
                case 403:
                    return $this->view->render($response, 'forbidden.twig', $data)->withStatus($statusCode);
                default:
                    return $this->view->render($response, 'error.twig', $data)->withStatus($statusCode);
            }
        } catch (Throwable $e) {
            return (new FatalErrorCtrl($this->di))->handleError($request, $response, $e);
        }
    }

    /**
     * @param Request $request
     * @param Throwable $exception
     * @param array $data
     */
    private function logError(Request $request, Throwable $exception, array $data)
    {
        // don't log 404s
        if ($data['status_code'] == 404) {
            return;
        }

        Debugger::log($exception, Debugger::EXCEPTION);
    }
}
