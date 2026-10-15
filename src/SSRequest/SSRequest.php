<?php

declare(strict_types=1);

namespace Newus\Satusehat\SSRequest;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use Newus\Satusehat\Http\HttpClientFactory;
use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSResponse\SSResponse;

class SSRequest
{
    private ?OAuth2Client $oauth2;

    private ?string $token;

    private string $baseUrl;

    /** @var array<string, mixed> */
    private array $config;

    private Client $http;

    /** @var array<string, mixed> */
    private array $defaultHeaders;

    private int $timeout;

    private int $maxRetries;

    private int $retryCount = 0;

    /**
     * @param $oauth2     OAuth2Client instance (token + baseUrl auto-resolved)
     * @param $token      Pre-provided bearer token (bypasses OAuth2Client)
     * @param $baseUrl    FHIR base URL override
     * @param array<string,mixed> $config    Optional: timeout, maxRetries, headers
     */
    public function __construct(
        ?OAuth2Client $oauth2 = null,
        ?string $token = null,
        ?string $baseUrl = null,
        array $config = []
    ) {
        $this->oauth2 = $oauth2;
        $this->token = $token;
        $this->config = array_merge(
            ['log_enabled' => function_exists('config') ? config('satusehatintegration.log_enabled', true) : true],
            $config
        );

        $this->timeout = (int) ($config['timeout'] ?? 30);
        $this->maxRetries = (int) ($config['maxRetries'] ?? 5);

        $this->defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/fhir+json',
        ];

        if (isset($config['headers']) && is_array($config['headers'])) {
            $this->defaultHeaders = array_merge($this->defaultHeaders, $config['headers']);
        }

        // Resolve base URL
        if ($baseUrl !== null) {
            $this->baseUrl = rtrim($baseUrl, '/');
        } elseif ($oauth2 !== null && ! empty($oauth2->fhir_url)) {
            $this->baseUrl = rtrim($oauth2->fhir_url, '/');
        } else {
            $this->baseUrl = '';
        }

        $httpConfig = [
            'timeout' => $this->timeout,
            'verify' => $config['verify'] ?? true,
        ];

        $this->http = HttpClientFactory::getClient($httpConfig);
    }

    // -------------------------------------------------------------------------
    // Public HTTP methods
    // -------------------------------------------------------------------------

    public function get(string $url, array $params = []): SSResponse
    {
        $fullUrl = $this->buildUrl($url, $params);

        return $this->request('GET', $fullUrl);
    }

    public function post(string $url, array $body): SSResponse
    {
        $fullUrl = $this->buildUrl($url);

        return $this->request('POST', $fullUrl, $body);
    }

    public function put(string $url, array $body): SSResponse
    {
        $fullUrl = $this->buildUrl($url);

        return $this->request('PUT', $fullUrl, $body);
    }

    public function delete(string $url, array $body = []): SSResponse
    {
        $fullUrl = $this->buildUrl($url);

        return $this->request('DELETE', $fullUrl, $body);
    }

    public function patch(string $url, array $patchPayload): SSResponse
    {
        $fullUrl = $this->buildUrl($url);

        return $this->request('PATCH', $fullUrl, $patchPayload);
    }

    /**
     * Cari pasien berdasarkan NIK (GET /Patient?identifier=https://fhir.kemkes.go.id/id/nik|{nik}).
     */
    public function getPatientByNik(string $nik): SSResponse
    {
        return $this->get('Patient', [
            'identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik,
        ]);
    }

    /**
     * Cari pasien bayi baru lahir berdasarkan NIK Ibu (GET /Patient?identifier=https://fhir.kemkes.go.id/id/nik-ibu|{nik_ibu}).
     */
    public function getPatientByNikIbu(string $nikIbu): SSResponse
    {
        return $this->get('Patient', [
            'identifier' => 'https://fhir.kemkes.go.id/id/nik-ibu|' . $nikIbu,
        ]);
    }

    /**
     * Cari pasien berdasarkan parameter kombinasi SATUSEHAT:
     * - name, birthdate, gender
     * - name, birthdate, nik
     * - name, nik
     * - nik atau nik_ibu
     *
     * @param array<string, mixed> $criteria
     */
    public function searchPatient(array $criteria): SSResponse
    {
        $params = [];

        if (! empty($criteria['nik'])) {
            $params['identifier'] = 'https://fhir.kemkes.go.id/id/nik|' . $criteria['nik'];
        } elseif (! empty($criteria['nik_ibu'])) {
            $params['identifier'] = 'https://fhir.kemkes.go.id/id/nik-ibu|' . $criteria['nik_ibu'];
        } elseif (! empty($criteria['identifier'])) {
            $params['identifier'] = $criteria['identifier'];
        }

        if (! empty($criteria['name'])) {
            $params['name'] = $criteria['name'];
        }
        if (! empty($criteria['birthdate'])) {
            $params['birthdate'] = $criteria['birthdate'];
        }
        if (! empty($criteria['gender'])) {
            $params['gender'] = $criteria['gender'];
        }

        return $this->get('Patient', $params);
    }

    /**
     * Send arbitrary request.
     *
     * @param string      $method  HTTP method (GET, POST, PUT, DELETE, PATCH)
     * @param string      $url     Absolute URL or path relative to FHIR base URL
     * @param $body    JSON payload (for POST, PUT, PATCH, DELETE)
     * @param $contentType Content-Type header override
     */
    public function request(string $method, string $url, ?array $body = null, ?string $contentType = null): SSResponse
    {
        $fullUrl = $this->buildUrl($url);
        $this->retryCount = 0;

        return $this->doRequest($method, $fullUrl, $body, $contentType);
    }

    // -------------------------------------------------------------------------
    // Execution engine with retry logic
    // -------------------------------------------------------------------------

    private function doRequest(string $method, string $url, ?array $body = null, ?string $contentType = null): SSResponse
    {
        $token = $this->getToken();

        $headers = $this->defaultHeaders;
        if ($token !== null) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        if ($method === 'PATCH') {
            $headers['Content-Type'] = $contentType ?? 'application/json-patch+json';
        } elseif ($contentType !== null) {
            $headers['Content-Type'] = $contentType;
        }

        $options = ['headers' => $headers];

        if ($body !== null && in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $options['body'] = json_encode($body, JSON_THROW_ON_ERROR);
        }

        try {
            $response = $this->http->request($method, $url, $options);

            return $this->buildResponse($response, $method, $url, $body);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            return $this->handleFailure($method, $url, $response, $body);
        } catch (\Throwable $e) {
            // Non-HTTP errors (connection, DNS, etc.) — return as error response
            return new SSResponse(
                0,
                ['resourceType' => 'OperationOutcome', 'issue' => [['details' => ['text' => $e->getMessage()]]]],
                $e->getMessage()
            );
        }
    }

    private function handleFailure(string $method, string $url, ResponseInterface $response, ?array $body = null): SSResponse
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        $bodyArray = json_decode($content, true) ?? [];

        // 429 — rate limited: read Retry-After header
        if ($statusCode === 429) {
            $this->retryCount++;

            if ($this->retryCount > $this->maxRetries) {
                return new SSResponse($statusCode, $bodyArray, $content);
            }

            $retryAfter = $response->getHeaderLine('Retry-After');
            $sleep = $retryAfter !== '' ? (int) $retryAfter : (2 ** $this->retryCount);

            $this->sleep($sleep);

            return $this->doRequest($method, $url, $body);
        }

        // 401 — unauthorized: attempt token refresh
        if ($statusCode === 401) {
            if ($this->isTokenAutoRefresh() && $this->oauth2 !== null) {
                $this->retryCount++;

                if ($this->retryCount > $this->maxRetries) {
                    return new SSResponse($statusCode, $bodyArray, $content);
                }

                $this->token = null;
                $this->sleep(2 ** $this->retryCount);

                return $this->doRequest($method, $url, $body);
            }

            return new SSResponse($statusCode, $bodyArray, $content);
        }

        // Log non-retried failures
        $this->logTransaction($method, $url, $body, $bodyArray, $statusCode);

        return new SSResponse($statusCode, $bodyArray, $content);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getToken(): ?string
    {
        if ($this->token !== null) {
            return $this->token;
        }

        if ($this->oauth2 !== null) {
            $tokenResult = $this->oauth2->token();

            return is_string($tokenResult) ? $tokenResult : null;
        }

        return null;
    }

    private function isTokenAutoRefresh(): bool
    {
        return (bool) ($this->config['auto_refresh_token'] ?? true);
    }

    private function buildUrl(string $url, array $params = []): string
    {
        // If absolute URL, keep as-is
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $fullUrl = $url;
        } else {
            $trimmedUrl = ltrim($url, '/');
            $fullUrl = $this->baseUrl !== '' ? $this->baseUrl . '/' . $trimmedUrl : $trimmedUrl;
        }

        if (! empty($params)) {
            $queryString = http_build_query($params);
            $separator = str_contains($fullUrl, '?') ? '&' : '?';
            $fullUrl .= $separator . $queryString;
        }

        return $fullUrl;
    }

    private function buildResponse(ResponseInterface $response, ?string $method = null, ?string $url = null, ?array $requestBody = null): SSResponse
    {
        $statusCode = $response->getStatusCode();
        $content = $response->getBody()->getContents();
        $bodyArray = json_decode($content, true) ?? [];

        $ssResponse = new SSResponse($statusCode, $bodyArray, $content);

        if ($method !== null && $url !== null) {
            $this->logTransaction($method, $url, $requestBody, $bodyArray, $statusCode);
        }

        return $ssResponse;
    }

    private function sleep(int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $seconds = min($seconds, 60);

        if (function_exists('sleep')) {
            sleep($seconds);
        }
    }

    private function logTransaction(string $action, string $url, ?array $payload, array $response, int $httpCode): void
    {
        if (! ($this->config['log_enabled'] ?? true)) {
            return;
        }

        if ($this->oauth2 !== null) {
            $this->oauth2->log('HTTP_' . $httpCode, $action, $url, $payload, $response);
        }
    }
}
