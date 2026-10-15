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

    public function testGetPatientByNikAndNikIbu(): void
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resourceType' => 'Bundle',
                'entry' => [
                    ['resource' => ['resourceType' => 'Patient', 'id' => 'P123']],
                ],
            ])),
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resourceType' => 'Bundle',
                'entry' => [
                    ['resource' => ['resourceType' => 'Patient', 'id' => 'P-BABY-456']],
                ],
            ])),
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resourceType' => 'Bundle',
                'total' => 1,
            ])),
        ]);

        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $client = new \GuzzleHttp\Client(['handler' => $handlerStack]);
        \Newus\Satusehat\Http\HttpClientFactory::setSharedClient($client);

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

        $resNik = $request->getPatientByNik('3171010101900001');
        $this->assertTrue($resNik->isSuccess());

        $resNikIbu = $request->getPatientByNikIbu('3171010101900001');
        $this->assertTrue($resNikIbu->isSuccess());

        $resSearch = $request->searchPatient([
            'name' => 'Budi',
            'birthdate' => '1990-01-01',
            'gender' => 'male',
        ]);
        $this->assertTrue($resSearch->isSuccess());

        \Newus\Satusehat\Http\HttpClientFactory::setSharedClient(null);
    }

    public function testGetPractitionerByNik(): void
    {
        $mock = new \GuzzleHttp\Handler\MockHandler([
            new \GuzzleHttp\Psr7\Response(200, ['Content-Type' => 'application/json'], json_encode([
                'resourceType' => 'Bundle',
                'entry' => [
                    ['resource' => ['resourceType' => 'Practitioner', 'id' => '10009880728']],
                ],
            ])),
        ]);

        $handlerStack = \GuzzleHttp\HandlerStack::create($mock);
        $client = new \GuzzleHttp\Client(['handler' => $handlerStack]);
        \Newus\Satusehat\Http\HttpClientFactory::setSharedClient($client);

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
        $res = $request->getPractitionerByNik('7209061211900001');

        $this->assertTrue($res->isSuccess());
        $this->assertSame(200, $res->getHttpCode());

        \Newus\Satusehat\Http\HttpClientFactory::setSharedClient(null);
    }
}
