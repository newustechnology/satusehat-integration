<?php

namespace Newus\Satusehat\Storage;

class NullLogStorage implements LogStorageInterface
{
    public function log(string $responseId, string $action, string $url, ?array $payload, $response): void
    {
        // No-op for standalone, testing, or when logging is disabled
    }
}
