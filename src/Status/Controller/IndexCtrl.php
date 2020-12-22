<?php

declare(strict_types=1);

namespace Status\Controller;

use DOMDocument;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Status\Utilities\Curl;

/**
 * Class IndexCtrl
 *
 * @package Status\Controller
 */
final class IndexCtrl extends BaseCtrl
{
    /**
     * @var int $siteTimeout
     */
    private int $siteTimeout = 3;

    /**
     * @var int $trackerTimeout
     */
    private int $trackerTimeout = 3;

    /**
     * @var int $ircTimeout
     */
    private int $ircTimeout = 2;

    /**
     * @var int $meiTimeout
     */
    private int $meiTimeout = 2;

    /**
     * @var int $cacheFor
     */
    private int $cacheFor = 15;

    /**
     * @return int
     */
    private function checkSite(): int
    {
        $curl = new Curl('https://animebytes.tv');
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status.animebytes.tv',
                CURLOPT_HTTPHEADER => ['Host: animebytes.tv', 'Connection: Close'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => $this->siteTimeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );
        $content = $curl->exec();
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        if (is_string($content)) {
            $doc = new DOMDocument();
            if (!@$doc->loadHTML($content)) { // unable to parse output, assume site is down
                $this->cache->doSet('site_status', 0, $this->cacheFor);
                return 0;
            }

            $nodes = $doc->getElementsByTagName('title');
            $title = $nodes->item(0)->nodeValue;
            if ($title === 'Down for Maintenance') {
                $this->cache->doSet('site_status', 2, $this->cacheFor);
                if ($this->trackerTimeout > 1) {
                    $this->trackerTimeout--;
                } // reduce trackerTimeout
                return 2;
            }
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->cache->doSet('site_status', 1, $this->cacheFor);
            return 1;
        }

        if ($this->trackerTimeout > 1) {
            $this->trackerTimeout--;
        } // site is down so reduce trackerTimeout
        $this->cache->doSet('site_status', 0, $this->cacheFor);
        return 0;
    }

    /**
     * @return int
     */
    private function checkMei(): int
    {
        $curl = new Curl('https://mei.animebytes.tv/images/error.jpg');
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status.animebytes.tv',
                CURLOPT_HTTPHEADER => ['Host: mei.animebytes.tv', 'Connection: Close'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => $this->meiTimeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true,
                CURLOPT_NOBODY => true,
            ]
        );
        $body = $curl->exec();
        if (!$body || $curl->error()) { // if there's no body (including headers) or there was error then its down
            unset($curl);
            $this->cache->doSet('mei_status', 0, $this->cacheFor);
            return 0;
        }
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        preg_match("/\r?\n(?:Location|URI): *(.*?) *\r?\n/im", $body, $headers);
        if ($httpCode === 302 && $headers[1] === '/error.jpg') {
            $this->cache->doSet('mei_status', 1, $this->cacheFor);
            return 1;
        }

        $this->cache->doSet('mei_status', 0, $this->cacheFor);
        return 0;
    }

    /**
     * @param string $ip
     *
     * @return int
     * @noinspection CurlSslServerSpoofingInspection
     */
    private function checkTrackerSingular(string $ip): int
    {
        $curl = new Curl("https://$ip/check");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status.animebytes.tv',
                CURLOPT_HTTPHEADER => ['Host: tracker.animebytes.tv', 'Connection: Close'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => $this->trackerTimeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 0
            ]
        );
        $content = $curl->exec();
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        if ($httpCode >= 200 && $httpCode < 300 && is_string($content)) {
            $val = false === strpos($content, 'unavailable');
            return (int)$val;
        }
        return 0;
    }

    /**
     * @return array
     */
    private function checkTracker(): array
    {
        $nsRecords = $this->config['tracker.ns'];

        $working = false;
        $error = false;
        $details = [];
        foreach ($nsRecords as $nsName => $nsRecord) {
            $status = $this->checkTrackerSingular($nsRecord);
            if ($status) {
                $working = true;
            } else {
                $error = true;
                if ($this->trackerTimeout > 1) {
                    $this->trackerTimeout--;
                } // at least one tracker proxy is down so reduce timeout for others
            }
            $details[] = ['status' => $status, 'ip' => $nsName];
        }

        $this->cache->doSet('tracker_details', $details, $this->cacheFor);
        if ($working && $error) {
            $this->cache->doSet('tracker_status', 2, $this->cacheFor);
            return ['status' => 2, 'details' => $details];
        }

        if ($working && !$error) {
            $this->cache->doSet('tracker_status', 1, $this->cacheFor);
            return ['status' => 1, 'details' => $details];
        }

        if (!$working && $error) {
            $this->cache->doSet('tracker_status', 0, $this->cacheFor);
            return ['status' => 0, 'details' => $details];
        }

// assume error
        $this->cache->doSet('tracker_status', 0, $this->cacheFor);
        return ['status' => 0, 'details' => $details];
    }

    /**
     * @return int
     */
    private function checkIrc(): int
    {
        $nsRecord = dns_get_record('irc.animebytes.tv', DNS_A)[0]['ip'];
        if (!is_string($nsRecord)) {
            $this->cache->doSet('irc_status', 0, $this->cacheFor);
            return 0;
        }
        $file = @fsockopen($nsRecord, 7000, $errno, $errstr, $this->ircTimeout);
        if (!$file) {
            $status = 0;
        } else {
            fclose($file);
            $status = 1;
        }

        $this->cache->doSet('irc_status', $status, $this->cacheFor);
        return $status;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        $data = [
            'server_request' => $request
        ];
        $data['site_status'] = $this->cache->doGet('site_status') ?: $this->checkSite();
        if (!$this->cache->doGet('tracker_status') || !$this->cache->doGet('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->doGet('tracker_status');
            $data['tracker_details'] = $this->cache->doGet('tracker_details');
        }
        $data['irc_status'] =
            $this->cache->doGet('irc_status') ?: $this->checkIrc();
        $data['mei_status'] =
            $this->cache->doGet('mei_status') ?: $this->checkMei();

        return $this->view->render($response, 'index.twig', $data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     *
     * @return Response
     */
    public function indexJson(Request $request, Response $response, array $args): Response
    {
        $data = [];
        $data['site_status'] =
            $this->cache->doGet('site_status') ?: $this->checkSite();
        if (!$this->cache->doGet('tracker_status') || !$this->cache->doGet('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->doGet('tracker_status');
            $data['tracker_details'] = $this->cache->doGet('tracker_details');
        }
        $data['irc_status'] =
            $this->cache->doGet('irc_status') ?: $this->checkIrc();
        $data['mei_status'] =
            $this->cache->doGet('mei_status') ?: $this->checkMei();

        return $response->withJson($data, 200, JSON_NUMERIC_CHECK);
    }
}
