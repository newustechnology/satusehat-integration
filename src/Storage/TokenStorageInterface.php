<?php

namespace Newus\Satusehat\Storage;

interface TokenStorageInterface
{
    /**
     * Get stored access token for the given environment and client ID.
     *
     * @param string $environment STG (Sandbox/Staging) or PROD
     * @param string $clientId
     * @return string|null
     */
    public function get(string $environment, string $clientId): ?string;

    /**
     * Store access token with a time-to-live in seconds (default 3000 = 50 minutes).
     *
     * @param string $environment STG (Sandbox/Staging) or PROD
     * @param string $clientId
     * @param string $token
     * @param int $ttl Seconds until expiration
     * @return void
     */
    public function set(string $environment, string $clientId, string $token, int $ttl = 3000): void;

    /**
     * Invalidate/remove stored token.
     *
     * @param string $environment
     * @param string $clientId
     * @return void
     */
    public function forget(string $environment, string $clientId): void;
}
