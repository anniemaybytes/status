<?php

declare(strict_types=1);

namespace Status\CachedValue;

use Exception;
use JsonException;
use Status\Exception\TwitterException;
use TwitterAPIExchange;

/**
 * Class Tweets
 *
 * @package Status\CachedValue
 */
final class Tweets extends Base
{
    protected static function getCacheKey(mixed $param): string
    {
        return 'tweets/' . $param['twitter.uid'];
    }

    protected static function getCacheDuration(mixed $param): int
    {
        return 300;
    }

    /**
     * @param mixed $param
     *
     * @return array
     * @throws JsonException
     * @throws Exception
     */
    protected static function fetchValue(mixed $param): array
    {
        $settings = [
            'oauth_access_token' => $param['twitter.oauth_token'] ?? '',
            'oauth_access_token_secret' => $param['twitter.oauth_secret'] ?? '',
            'consumer_key' => $param['twitter.consumer_key'] ?? '',
            'consumer_secret' => $param['twitter.consumer_secret'] ?? ''
        ];
        $options = [
            'user_id' => $param['twitter.uid'] ?? 783214,
            'count' => $param['twitter.count'] ?? 10,
            'tweet_mode' => 'extended',
            'trim_user' => true,
            'exclude_replies' => true,
            'include_rts' => true,
        ];

        $twitter = new TwitterAPIExchange($settings);
        $feeds = json_decode(
            $twitter
                ->setGetfield('?' . http_build_query($options))
                ->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json', 'GET')
                ->performRequest(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        if (isset($feeds['errors'])) {
            throw new TwitterException(json_encode($feeds['errors'], JSON_THROW_ON_ERROR));
        }
        return $feeds;
    }
}
