<?php

namespace Newus\Satusehat\Tests\Unit;

use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRequest\SSRequest;
use Newus\Satusehat\SSResponse\SSResponse;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;

class SSRequestTest extends TestCase
{
    public function testSSRequestInitializesWithStandaloneOAuth2Client(): void
    {
        $storage = new MemoryTokenStorage();
        $storage->set('DEV', 'dev_id', 'mock_token', 3000);

        $oauth = new OAuth2Client([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => 'dev_org',
            'base_url' => 'https://api-satusehat-dev.dto.kemkes.go.id',
            'token_storage' => $storage,
        ]);

        $request = new SSRequest($oauth);
        $this->assertInstanceOf(SSRequest::class, $request);
    }

    public function testSSResponseMethods(): void
    {
        $payload = [
            'resourceType' => 'Patient',
            'id' => 'patient-123',
            'name' => [['text' => 'Budi']],
        ];

        $response = new SSResponse(200, $payload, json_encode($payload));

        $this->assertTrue($response->isSuccess());
        $this->assertFalse($response->isError());
        $this->assertSame(200, $response->getHttpCode());
        $this->assertSame('patient-123', $response->getResourceId());
        $this->assertSame('patient-123', $response->get('Patient'));
        $this->assertSame($payload, $response->json());
    }

    public function testSSResponseOperationOutcomeErrors(): void
    {
        $payload = [
            'resourceType' => 'OperationOutcome',
            'issue' => [
                ['details' => ['text' => 'NIK tidak ditemukan']],
            ],
        ];

        $response = new SSResponse(404, $payload, json_encode($payload));

        $this->assertFalse($response->isSuccess());
        $this->assertTrue($response->isError());
        $this->assertSame(['NIK tidak ditemukan'], $response->getErrorMessages());
    }
}
