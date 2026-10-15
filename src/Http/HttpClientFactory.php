<?php

namespace Newus\Satusehat\Http;

use GuzzleHttp\Client;

class HttpClientFactory
{
    private static ?Client $sharedClient = null;

    /**
     * Get a pooled/shared Guzzle HTTP client with keep-alive connection reuse.
     *
     * @param array<string, mixed> $config
     * @return Client
     */
    public static function getClient(array $config = []): Client
    {
        if (self::$sharedClient !== null) {
            return self::$sharedClient;
        }

        $defaultConfig = [
            'timeout' => 30.0,
            'connect_timeout' => 10.0,
            'http_errors' => false,
            'headers' => [
                'Connection' => 'keep-alive',
                'User-Agent' => 'Newus-Satusehat-Integration/1.0',
            ],
        ];

        $mergedConfig = array_merge($defaultConfig, $config);
        $client = new Client($mergedConfig);

        if (empty($config)) {
            self::$sharedClient = $client;
        }

        return $client;
    }

    /**
     * Set or reset the shared client (useful for unit testing and mocking).
     *
     * @param $client
     * @return void
     */
    public static function setSharedClient(?Client $client): void
    {
        self::$sharedClient = $client;
    }
}
