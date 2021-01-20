<?php

declare(strict_types=1);

namespace Status\Controller;

use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Status\CachedValue\Irc;
use Status\CachedValue\Mei;
use Status\CachedValue\Site;
use Status\CachedValue\TrackerSingular;
use Status\CachedValue\Tweets;
use Tracy\Debugger;

/**
 * Class IndexCtrl
 *
 * @package Status\Controller
 */
final class IndexCtrl extends BaseCtrl
{
    private function checkTracker(): array
    {
        $nsRecords = $this->config['tracker.ns'];

        $working = false;
        $error = false;
        $details = [];
        foreach ($nsRecords as $nsName => $nsRecord) {
            $status = TrackerSingular::get(
                $this->cache,
                ['ns' => $nsRecord, 'domain' => $this->config['tracker.domain']]
            );
            if ($status) {
                $working = true;
            } else {
                $error = true;
            }
            $details[] = ['status' => $status, 'ip' => $nsName];
        }

        if ($working && $error) {
            return ['status' => 2, 'details' => $details];
        }
        if ($working && !$error) {
            return ['status' => 1, 'details' => $details];
        }
        if (!$working && $error) {
            return ['status' => 0, 'details' => $details];
        }

        return ['status' => 0, 'details' => $details];
    }

    private function getStatus(): array
    {
        $tr = $this->checkTracker();
        $data['tracker_status'] = $tr['status'];
        $data['tracker_details'] = $tr['details'];

        $data['site_status'] = Site::get($this->cache, $this->config['site.domain']);
        $data['irc_status'] = Irc::get($this->cache, $this->config['irc.domain']);
        $data['mei_status'] = Mei::get($this->cache, $this->config['mei.domain']);

        return $data;
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $data = $this->getStatus();
        $data['server_request'] = $request;

        if ($this->config['twitter.enabled']) {
            try {
                $feeds = Tweets::get($this->cache, $this->config);
                if (count($feeds)) {
                    $data['twitter_feed'] = $feeds;
                }
            } catch (Exception $e) {
                Debugger::log($e, Debugger::WARNING);
            }
        }

        return $this->view->render($response, 'index.twig', $data);
    }

    public function indexJson(Request $request, Response $response, array $args): Response
    {
        return $response->withJson($this->getStatus(), 200, JSON_NUMERIC_CHECK);
    }
}
