<?php

declare(strict_types=1);

namespace Status\Utilities;

use JsonException;
use Status\Exception\TwitterException;

/**
 * Class Twitter
 *
 * @package Status\Utilities
 */
final class Twitter
{
    private const BEARER = 'AAAAAAAAAAAAAAAAAAAAAPYXBAAAAAAACLXUNDekMxqa8h%2F40K4moUkGsoc%3DTYfbDKbT3jJPCEVnMYqilB28NHfOPqkca3qaAxGfsyKCs0wRbw';

    private ?string $guestToken = null;

    /** @throws TwitterException */
    public function getUserIdByName(string $username): int
    {
        /** @noinspection JsonEncodingApiUsageInspection */
        $vars = urlencode(json_encode(['screen_name' => $username, 'withHighlightedLabel' => true]));

        $curl = new Curl("https://api.twitter.com/graphql/4S2ihIKfF3xhp-ENxvUAfQ/UserByScreenName?variables=$vars");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status (cURL) like Twitterbot/1.0',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . self::BEARER,
                    'X-Guest-Token: ' . $this->guestToken()
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );

        if (!$content = $curl->exec()) {
            if (!$err = $curl->error()) {
                throw new TwitterException(
                    "Received empty response from remote API with HTTP code {$curl->getInfo(CURLINFO_HTTP_CODE)}"
                );
            }
            throw new TwitterException("Failed to query remote API: $err");
        }
        unset($curl);

        try {
            $user = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TwitterException("Failed to read API response as JSON object", 0, $e);
        }

        if (isset($user['errors'])) {
            /** @noinspection JsonEncodingApiUsageInspection */
            throw new TwitterException(json_encode($user['errors'], JSON_PARTIAL_OUTPUT_ON_ERROR));
        }

        return (int)$user['data']['user']['rest_id'];
    }

    /** @throws TwitterException */
    private function guestToken(): string
    {
        if ($this->guestToken) {
            return $this->guestToken; // keep using current token
        }

        $curl = new Curl('https://api.twitter.com/1.1/guest/activate.json');
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status (cURL) like Twitterbot/1.0',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . self::BEARER
                ],
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );

        if (!$content = $curl->exec()) {
            if (!$err = $curl->error()) {
                throw new TwitterException(
                    "Received empty response from remote API with HTTP code {$curl->getInfo(CURLINFO_HTTP_CODE)}"
                );
            }
            throw new TwitterException("Failed to query remote API: $err");
        }
        unset($curl);

        try {
            $response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TwitterException("Failed to read API response as JSON object", 0, $e);
        }

        if (isset($response['errors'])) {
            /** @noinspection JsonEncodingApiUsageInspection */
            throw new TwitterException(json_encode($response['errors'], JSON_PARTIAL_OUTPUT_ON_ERROR));
        }

        return $response["guest_token"];
    }

    /**
     * @return array{user: array, tweets: array}
     * @throws TwitterException
     */
    public function getTimeline(int $uid, int $count): array
    {
        $curl = new Curl("https://api.twitter.com/2/timeline/profile/$uid.json?tweet_mode=extended&count=$count");
        $curl->setoptArray(
            [
                CURLOPT_USERAGENT => 'status (cURL) like Twitterbot/1.0',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . self::BEARER,
                    'X-Guest-Token: ' . $this->guestToken()
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => false,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_VERIFYHOST => 2
            ]
        );

        if (!$content = $curl->exec()) {
            if (!$err = $curl->error()) {
                throw new TwitterException(
                    "Received empty response from remote API with HTTP code {$curl->getInfo(CURLINFO_HTTP_CODE)}"
                );
            }
            throw new TwitterException("Failed to query remote API: $err");
        }
        unset($curl);

        try {
            $feeds = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new TwitterException("Failed to read API response as JSON object", 0, $e);
        }

        if (isset($feeds['errors'])) {
            /** @noinspection JsonEncodingApiUsageInspection */
            throw new TwitterException(json_encode($feeds['errors'], JSON_PARTIAL_OUTPUT_ON_ERROR));
        }

        krsort($feeds['globalObjects']['tweets'], SORT_NUMERIC); // sort tweets by their id
        return [
            'user' => $feeds['globalObjects']['users'][$uid],
            'tweets' => $feeds['globalObjects']['tweets']
        ];
    }
}
