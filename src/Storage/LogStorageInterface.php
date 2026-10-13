<?php

namespace Newus\Satusehat\Storage;

interface LogStorageInterface
{
    /**
     * Record a SATUSEHAT API transaction log.
     *
     * @param string $responseId
     * @param string $action
     * @param string $url
     * @param $payload
     * @param $response
     * @return void
     */
    public function log(string $responseId, string $action, string $url, ?array $payload, $response): void;
}
