<?php declare(strict_types=1);

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
class IndexCtrl extends BaseCtrl
{
    /** @var int $siteTimeout */
    private $siteTimeout = 3;
    /** @var int $trackerTimeout */
    private $trackerTimeout = 3;
    /** @var int $ircTimeout */
    private $ircTimeout = 2;
    /** @var int $meiTimeout */
    private $meiTimeout = 2;

    /** @var int $cacheFor */
    private $cacheFor = 15;

    /**
     * @return int
     */
    private function checkSite(): int
    {
        $curl = new Curl("https://animebytes.tv");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => "status.animebytes.tv",
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
            @$doc->loadHTML($content);
            if (!$doc) { // unable to parse output, assume site is down
                $this->cache->add('site_status', 0, $this->cacheFor);
                return (int)0;
            }

            $nodes = $doc->getElementsByTagName('title');
            $title = $nodes->item(0)->nodeValue;
            if ($title === 'Down for Maintenance') {
                $this->cache->add('site_status', 2, $this->cacheFor);
                if ($this->trackerTimeout > 1) {
                    $this->trackerTimeout--;
                } // reduce trackerTimeout
                return (int)2;
            }
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->cache->add('site_status', 1, $this->cacheFor);
            return (int)1;
        }

        if ($this->trackerTimeout > 1) {
            $this->trackerTimeout--;
        } // site is down so reduce trackerTimeout
        $this->cache->add('site_status', 0, $this->cacheFor);
        return (int)0;
    }

    /**
     * @return int
     */
    private function checkMei(): int
    {
        $curl = new Curl("https://mei.animebytes.tv/images/error.jpg");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => "status.animebytes.tv",
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
        $httpCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        unset($curl);

        preg_match("/\r?\n(?:Location|URI): *(.*?) *\r?\n/im", $body, $headers);
        if ($httpCode === 302 && $headers[1] === '/error.jpg') {
            $this->cache->add('mei_status', 1, $this->cacheFor);
            return (int)1;
        }

        $this->cache->add('mei_status', 0, $this->cacheFor);
        return (int)0;
    }

    /**
     * @param string $ip
     *
     * @return int
     */
    private function checkTrackerSingular(string $ip): int
    {
        $curl = new Curl("https://$ip");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => "status.animebytes.tv",
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
            $val = !preg_match('/unavailable/', $content);
            return (int)$val;
        }
        return (int)0;
    }

    /**
     * @return array
     */
    private function checkTracker(): array
    {
        $nsRecords = $this->di->get('config')['tracker.ns'];

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
            $details[] = ['status' => (int)$status, 'ip' => $nsName];
        }

        $this->cache->add('tracker_details', $details, $this->cacheFor);
        if ($working && $error) {
            $this->cache->add('tracker_status', 2, $this->cacheFor);
            return ['status' => (int)2, 'details' => $details];
        } elseif ($working && !$error) {
            $this->cache->add('tracker_status', 1, $this->cacheFor);
            return ['status' => (int)1, 'details' => $details];
        } elseif (!$working && $error) {
            $this->cache->add('tracker_status', 0, $this->cacheFor);
            return ['status' => (int)0, 'details' => $details];
        } else { // assume error
            $this->cache->add('tracker_status', 0, $this->cacheFor);
            return ['status' => (int)0, 'details' => $details];
        }
    }

    /**
     * @return int
     */
    private function checkIrc()
    {
        $nsRecord = dns_get_record("irc.animebytes.tv", DNS_A)[0]['ip'];
        if (!is_string($nsRecord)) {
            $this->cache->add('irc_status', 0, $this->cacheFor);
            return 0;
        }
        $file = @fsockopen($nsRecord, 80, $errno, $errstr, $this->ircTimeout);
        if (!$file) {
            $status = 0;
        } else {
            fclose($file);
            $status = 1;
        }

        $this->cache->add('irc_status', $status, $this->cacheFor);
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
        $data = [];
        $data['site_status'] = $this->cache->exists('site_status') ? $this->cache->fetch(
            'site_status'
        ) : $this->checkSite();
        if (!$this->cache->exists('tracker_status') || !$this->cache->exists('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->fetch('tracker_status');
            $data['tracker_details'] = $this->cache->fetch('tracker_details');
        }
        $data['irc_status'] =
            $this->cache->exists('irc_status') ? $this->cache->fetch('irc_status') : $this->checkIrc();
        $data['mei_status'] =
            $this->cache->exists('mei_status') ? $this->cache->fetch('mei_status') : $this->checkMei();

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
            $this->cache->exists('site_status') ? $this->cache->fetch('site_status') : $this->checkSite();
        if (!$this->cache->exists('tracker_status') || !$this->cache->exists('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->fetch('tracker_status');
            $data['tracker_details'] = $this->cache->fetch('tracker_details');
        }
        $data['irc_status'] =
            $this->cache->exists('irc_status') ? $this->cache->fetch('irc_status') : $this->checkIrc();
        $data['mei_status'] =
            $this->cache->exists('mei_status') ? $this->cache->fetch('mei_status') : $this->checkMei();

        return $response->withJson($data, 200, JSON_NUMERIC_CHECK);
    }
}
