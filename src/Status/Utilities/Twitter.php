<?php

declare(strict_types=1);

namespace Status\Utilities;

use DOMDocument;
use JsonException;
use Status\Exception\CurlException;
use Status\Exception\TwitterException;

/**
 * Class Twitter
 *
 * @package Status\Utilities
 */
final class Twitter
{
    /**
     * @return array{user: array, tweets: array}
     * @throws TwitterException|CurlException
     */
    public static function getTimeline(string $username): array
    {
        $curl = new Curl("https://syndication.twitter.com/srv/timeline-profile/screen-name/$username");
        if (!$content = $curl->exec()) {
            if (!$err = $curl->error()) {
                throw new CurlException(
                    "Received empty response from Twitter with HTTP code {$curl->getInfo(CURLINFO_HTTP_CODE)}"
                );
            }
            throw new CurlException("Failed to fetch data from Twitter: $err");
        }

        $doc = new DOMDocument();
        if (!@$doc->loadHTML($content)) {
            throw new TwitterException("Failed to load remote response as DOMDocument");
        }

        if (!$data = $doc->getElementById('__NEXT_DATA__')) {
            throw new TwitterException("Failed to locate __NEXT_DATA__ element in remote DOMDocument");
        }

        try {
            $feeds = json_decode($data->textContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TwitterException("Failed to read __NEXT_DATA__ element as JSON object", 0, $e);
        }

        if (!$props = @$feeds['props']['pageProps']) {
            throw new TwitterException("JSON object does not contain expected properties");
        }

        $tweets = [];
        foreach (@$props['timeline']['entries'] ?? [] as $entry) {
            if (@$entry['type'] !== 'tweet') {
                continue;
            }
            $tweets[$entry['sort_index']] = $entry['content']['tweet'];
        }

        krsort($tweets, SORT_NUMERIC); // sort tweets by their id
        return [
            'user' => $props['headerProps'] ?? ['screenName' => $username],
            'tweets' => $tweets
        ];
    }
}
