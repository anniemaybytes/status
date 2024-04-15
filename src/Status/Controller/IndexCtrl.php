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
use Status\Enum\Status;
use Status\Exception\TwitterException;
use Tracy\Debugger;

/**
 * Class IndexCtrl
 *
 * @package Status\Controller
 */
final class IndexCtrl extends BaseCtrl
{
    public function index(Request $request, Response $response, array $args): Response
    {
        $data = $this->getStatus();

        if ($this->config['twitter.enabled']) {
            try {
                $feeds = Tweets::get($this->cache, $this->config['twitter.username']);
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

    /**
     * @return array{
     *     site: array{status: int},
     *     tracker: array{status: int, details: array<array{status: int, ip: string}>},
     *     irc: array{status: int},
     *     mei: array{status: int}
     *   }
     */
    private function getStatus(): array
    {
        return [
            'site' => ['status' => Site::get($this->cache, $this->config['site.canonical'])],
            'tracker' => (function () {
                $working = 0;
                $details = [];

                foreach ($this->config['tracker.ns'] as $alias => $address) {
                    if (
                        $status = TrackerSingular::get(
                            $this->cache,
                            ['domain' => $this->config['tracker.domain'], 'ns' => $address]
                        )
                    ) {
                        $working++;
                    }

                    $details[] = ['status' => $status, 'ip' => $alias];
                }

                if ($working === count($this->config['tracker.ns'])) {
                    return ['status' => Status::NORMAL->value, 'details' => $details];
                }
                if ($working > 0 && $working < count($this->config['tracker.ns'])) {
                    return ['status' => Status::ISSUES->value, 'details' => $details];
                }

                return ['status' => Status::DOWN->value, 'details' => $details];
            })(),
            'irc' => [
                'status' => Irc::get(
                    $this->cache,
                    ['domain' => $this->config['irc.domain'], 'port' => $this->config['irc.port']]
                )
            ],
            'mei' => ['status' => Mei::get($this->cache, $this->config['mei.canonical'])]
        ];
    }
}
