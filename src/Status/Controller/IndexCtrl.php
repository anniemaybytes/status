<?php

declare(strict_types=1);

namespace Status\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Status\CachedValue\Irc;
use Status\CachedValue\Mei;
use Status\CachedValue\Site;
use Status\CachedValue\TrackerSingular;
use Status\CachedValue\Tweets;
use Status\Exception\TwitterException;
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
        $nsRecords = $this->config['tracker.ns'] ?? ['localhost' => '10.0.0.1'];

        $working = 0;
        $details = [];
        foreach ($nsRecords as $nsName => $nsRecord) {
            $status = TrackerSingular::get(
                $this->cache,
                ['ns' => $nsRecord, 'domain' => $this->config['tracker.domain'] ?? 'tracker.animebytes.local']
            );
            if ($status) {
                $working++;
            }
            $details[] = ['status' => $status, 'ip' => $nsName];
        }

        if ($working === count($nsRecords)) {
            return ['status' => 1, 'details' => $details]; // all good
        }
        if ($working > 0 && $working < count($nsRecords)) {
            return ['status' => 2, 'details' => $details]; // issues
        }

        return ['status' => 0, 'details' => $details]; // not working
    }

    private function getStatus(): array
    {
        $tr = $this->checkTracker();
        $data['tracker'] = [
            'status' => $tr['status'],
            'details' => $tr['details']
        ];
        $data['site'] = [
            'status' => Site::get($this->cache, $this->config['site.domain'] ?? 'animebytes.local:9443')
        ];
        $data['irc'] = [
            'status' => Irc::get($this->cache, $this->config['irc.domain'] ?? 'irc.animebytes.local')
        ];
        $data['mei'] = [
            'status' => Mei::get($this->cache, $this->config['mei.domain'] ?? 'mei.animebytes.local:8443')
        ];

        return $data;
    }

    public function index(Request $request, Response $response, array $args): Response
    {
        $data = $this->getStatus();
        $data['server_request'] = $request;

        if ($this->config['twitter.enabled'] ?? false) {
            try {
                $feeds = Tweets::get($this->cache, $this->config);
                if (count($feeds)) {
                    $data['twitter_feed'] = $feeds;
                }
            } /** @noinspection PhpRedundantCatchClauseInspection */ catch (TwitterException $e) {
                Debugger::log($e, Debugger::WARNING);
            }
        }

        return $this->view->render($response, 'index.twig', $data);
    }

    public function json(Request $request, Response $response, array $args): Response
    {
        return $response->withJson(['success' => true, 'status' => $this->getStatus()], 200, JSON_NUMERIC_CHECK);
    }
}
