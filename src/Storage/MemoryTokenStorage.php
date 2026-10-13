<?php

namespace Newus\Satusehat\Storage;

class MemoryTokenStorage implements TokenStorageInterface
{
    /** @var array<string, array{token: string, expires_at: int}> */
    private static array $tokens = [];

    public function get(string $environment, string $clientId): ?string
    {
        $key = $environment . ':' . $clientId;
        if (! isset(self::$tokens[$key])) {
            return null;
        }

        if (time() >= self::$tokens[$key]['expires_at']) {
            unset(self::$tokens[$key]);
            return null;
        }

        return self::$tokens[$key]['token'];
    }

    public function set(string $environment, string $clientId, string $token, int $ttl = 3000): void
    {
        $key = $environment . ':' . $clientId;
        self::$tokens[$key] = [
            'token' => $token,
            'expires_at' => time() + $ttl,
        ];
    }

    public function forget(string $environment, string $clientId): void
    {
        $key = $environment . ':' . $clientId;
        unset(self::$tokens[$key]);
    }

    public static function clear(): void
    {
        self::$tokens = [];
    }
}
