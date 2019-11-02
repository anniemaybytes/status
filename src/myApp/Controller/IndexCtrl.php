<?php

namespace myApp\Controller;

use DOMDocument;
use Slim\Http\Request;
use Slim\Http\Response;

class IndexCtrl extends BaseCtrl
{
    private $siteTimeout = 3;
    private $trackerTimeout = 3;
    private $ircTimeout = 2;
    private $meiTimeout = 2;

    private $cacheFor = 15;

    private function checkSite()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://animebytes.tv");
        curl_setopt($ch, CURLOPT_USERAGENT, "status.animebytes.tv");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: animebytes.tv', 'Connection: Close']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->siteTimeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($content) {
            $doc = new DOMDocument();
            @$doc->loadHTML($content);
            $nodes = $doc->getElementsByTagName('title');
            $title = $nodes->item(0)->nodeValue;
            if ($title === 'Down for Maintenance') {
                $this->cache->add('site_status', (int)2, $this->cacheFor);
                if ($this->trackerTimeout > 1) $this->trackerTimeout--; // reduce trackerTimeout
                return (int)2;
            }
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            $this->cache->add('site_status', (int)1, $this->cacheFor);
            return (int)1;
        }

        if ($this->trackerTimeout > 1) $this->trackerTimeout--; // site is down so reduce trackerTimeout
        $this->cache->add('site_status', (int)0, $this->cacheFor);
        return (int)0;
    }

    private function checkMei()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://mei.animebytes.tv");
        curl_setopt($ch, CURLOPT_USERAGENT, "status.animebytes.tv");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: mei.animebytes.tv', 'Connection: Close']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->meiTimeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 404 && $content) {
            if ($content === '404 - Route Not Found') {
                $this->cache->add('mei_status', (int)1, $this->cacheFor);
                return (int)1;
            }
        }

        $this->cache->add('mei_status', (int)0, $this->cacheFor);
        return (int)0;
    }

    private function checkTrackerSingular($ip)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://$ip");
        curl_setopt($ch, CURLOPT_USERAGENT, "status.animebytes.tv");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Host: tracker.animebytes.tv', 'Connection: Close']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->trackerTimeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300) {
            $val = !preg_match('/unavailable/', $content);
            return (int)$val;
        }
        return (int)0;
    }

    private function checkTracker()
    {
        $nsRecords = $this->di['config']['tracker.ns'];

        $working = false;
        $error = false;
        $details = [];
        foreach ($nsRecords as $nsName => $nsRecord) {
            $status = $this->checkTrackerSingular($nsRecord);
            if ($status) {
                $working = true;
            } else {
                $error = true;
                if ($this->trackerTimeout > 1) $this->trackerTimeout--; // at least one tracker proxy is down so reduce timeout for others
            }
            $details[] = ['status' => (int)$status, 'ip' => $nsName];
        }

        $this->cache->add('tracker_details', $details, $this->cacheFor);
        if ($working && $error) {
            $this->cache->add('tracker_status', (int)2, $this->cacheFor);
            return ['status' => (int)2, 'details' => $details];
        } else if ($working && !$error) {
            $this->cache->add('tracker_status', (int)1, $this->cacheFor);
            return ['status' => (int)1, 'details' => $details];
        } else if (!$working && $error) {
            $this->cache->add('tracker_status', (int)0, $this->cacheFor);
            return ['status' => (int)0, 'details' => $details];
        } else { // assume error
            $this->cache->add('tracker_status', (int)0, $this->cacheFor);
            return ['status' => (int)0, 'details' => $details];
        }
    }

    private function checkIrc()
    {
        $nsRecord = dns_get_record("irc.animebytes.tv", DNS_A)[0]['ip'];
        $file = @fsockopen($nsRecord, 80, $errno, $errstr, $this->ircTimeout);
        if (!$file) {
            $status = (int)0;
        } else {
            fclose($file);
            $status = (int)1;
        }

        $this->cache->add('irc_status', $status, $this->cacheFor);
        return $status;
    }

    public function index(Request $request, Response $response)
    {
        $data = [];
        $data['site_status'] = $this->cache->exists('site_status') ? $this->cache->fetch('site_status') : $this->checkSite();
        if (!$this->cache->exists('tracker_status') || !$this->cache->exists('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->fetch('tracker_status');
            $data['tracker_details'] = $this->cache->fetch('tracker_details');
        }
        $data['irc_status'] = $this->cache->exists('irc_status') ? $this->cache->fetch('irc_status') : $this->checkIrc();
        $data['mei_status'] = $this->cache->exists('mei_status') ? $this->cache->fetch('mei_status') : $this->checkMei();

        return $this->view->render($response, 'index.twig', $data);
    }

    public function indexJson(Request $request, Response $response)
    {
        $data = [];
        $data['site_status'] = $this->cache->exists('site_status') ? $this->cache->fetch('site_status') : $this->checkSite();
        if (!$this->cache->exists('tracker_status') || !$this->cache->exists('tracker_details')) {
            $tr = $this->checkTracker();
            $data['tracker_status'] = $tr['status'];
            $data['tracker_details'] = $tr['details'];
        } else {
            $data['tracker_status'] = $this->cache->fetch('tracker_status');
            $data['tracker_details'] = $this->cache->fetch('tracker_details');
        }
        $data['irc_status'] = $this->cache->exists('irc_status') ? $this->cache->fetch('irc_status') : $this->checkIrc();
        $data['mei_status'] = $this->cache->exists('mei_status') ? $this->cache->fetch('mei_status') : $this->checkMei();

        return $response->withJson($data, 200, JSON_NUMERIC_CHECK);
    }
}
