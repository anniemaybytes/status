<?php
namespace myApp\Controller;

use Exception;
use myApp\Exception\NotFound;
use Slim\Http\Request;
use Slim\Http\Response;

class ErrorCtrl extends BaseCtrl
{
    private function getData($statusCode) {
        $data = array();
        $data['status_code'] = $statusCode;

        return $data;
    }

    public function handleException(Request $request, Response $response, $exception)
    {
        // make sure we don't throw an exception
        try {
            $statusCode = 500;
            if ($exception instanceof NotFound) {
                $statusCode = 404;
            }

            $data = $this->getData($statusCode);

            $this->logError($request, $exception, $data);

            // clear the body first
            $body = $response->getBody();
            $body->rewind();
            $response = $response->withBody($body);

            switch($statusCode)
            {
                case 404:
                    return $this->view->render($response, 'not_found.twig', $data)->withStatus($statusCode);
                case 403:
                    return $this->view->render($response, 'forbidden.twig', $data)->withStatus($statusCode);
                default:
                    return $this->view->render($response, 'error.twig', $data)->withStatus($statusCode);
            }
        }
        catch (Exception $e) {
            error_log('Caught exception in exception handler - ' . $e->getFile() . '(' . $e->getLine() . ') ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $response->getBody()->write('Something broke. Sorry.');
            return $response->withStatus(500);
        }
    }

    private function logError(Request $request, Exception $exception, $data)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $fragment = $uri->getFragment();
        $path =  $path . ($query ? '?' . $query : '') . ($fragment ? '#' . $fragment : '');
        $method = $request->getMethod();
        $referrer = $request->getHeaderLine('HTTP_REFERER');
        $ua = $request->getHeaderLine('HTTP_USER_AGENT');
        $bt = '';

        $prefix =  "Error: {$data['status_code']} Method: $method $path ";
        if ($referrer) {
            $prefix .= '(referrer: ' . $referrer . ') ';
        }
        $msg = $prefix . ' - ' . $data['status_message'] . ' - ' . $ua;
        $msg = sprintf("%s\n%s:%s", $msg, $exception->getFile(), $exception->getLine());

        $ErrorData = sprintf("%s\nMessage:%s\nBacktrace:\n%s", $msg, $exception->getMessage(), $bt);

        // don't log 404s
        if ($data['status_code'] == 404) {
            return;
        }

        error_log($ErrorData);
    }
}
