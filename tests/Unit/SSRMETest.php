<?php

declare(strict_types=1);

namespace Newus\Satusehat\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Newus\Satusehat\Exception\Helper\SSRMEException;
use Newus\Satusehat\Http\HttpClientFactory;
use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRME;
use Newus\Satusehat\Storage\MemoryTokenStorage;
use PHPUnit\Framework\TestCase;

class SSRMETest extends TestCase
{
    private function createMockSSRME(array $mockResponses = []): SSRME
    {
        $storage = new MemoryTokenStorage();
        $storage->set('DEV', 'dev_id', 'mock_token', 3000);

        if (! empty($mockResponses)) {
            $mock = new MockHandler($mockResponses);
            $handlerStack = HandlerStack::create($mock);
            $client = new Client(['handler' => $handlerStack]);
            HttpClientFactory::setSharedClient($client);
        }

        return new SSRME([
            'environment' => 'DEV',
            'client_id' => 'dev_id',
            'client_secret' => 'dev_secret',
            'organization_id' => '10000004',
            'organization_name' => 'RS Satusehat',
            'base_url' => 'https://api-satusehat-dev.dto.kemkes.go.id',
            'token_storage' => $storage,
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        HttpClientFactory::setSharedClient(null);
    }

    public function testSSRMEInitializesWithArrayConfig(): void
    {
        $ssrme = $this->createMockSSRME();

        $this->assertSame('DEV', $ssrme->satusehat_env);
        $this->assertSame('10000004', $ssrme->organization_id);
        $this->assertSame('RS Satusehat', $ssrme->organization_name);
        $this->assertSame('https://api-satusehat-dev.dto.kemkes.go.id/ssrme/v2/rct/chl', $ssrme->getChlinkUrl());
        $this->assertSame('https://api-satusehat-dev.dto.kemkes.go.id/ssrme/v2/rct/shl', $ssrme->getShlinkUrl());
    }

    public function testSSRMEInitializesWithExistingOAuth2Client(): void
    {
        $storage = new MemoryTokenStorage();
        $storage->set('STG', 'stg_id', 'mock_token', 3000);

        $oauth = new OAuth2Client([
            'environment' => 'STG',
            'client_id' => 'stg_id',
            'client_secret' => 'stg_secret',
            'organization_id' => 'org_stg_1',
            'organization_name' => 'Klinik Sehat',
            'base_url' => 'https://api-satusehat-stg.dto.kemkes.go.id',
            'token_storage' => $storage,
        ]);

        $ssrme = new SSRME($oauth);

        $this->assertSame('STG', $ssrme->satusehat_env);
        $this->assertSame('org_stg_1', $ssrme->organization_id);
        $this->assertSame('Klinik Sehat', $ssrme->organization_name);
        $this->assertSame('https://api-satusehat-stg.dto.kemkes.go.id/ssrme/v2/rct/chl', $ssrme->getChlinkUrl());
    }

    public function testBuildPayloadNormal(): void
    {
        $ssrme = $this->createMockSSRME();

        $payload = $ssrme->buildPayload([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);

        $this->assertSame('P20066076728', $payload['patient_id']);
        $this->assertSame('Tn. Asep Mulyono', $payload['patient_name']);
        $this->assertSame('10006000010', $payload['practitioner_id']);
        $this->assertSame('dr. Ratna Dewi, Sp.M', $payload['practitioner_name']);
        $this->assertSame('10000004', $payload['organization_id']);
        $this->assertSame('RS Satusehat', $payload['organization_name']);
        $this->assertArrayNotHasKey('type_medical_summary', $payload);
    }

    public function testBuildPayloadEmergency(): void
    {
        $ssrme = $this->createMockSSRME();

        $payload = $ssrme->buildPayload([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
            'emergency' => true,
        ]);

        $this->assertSame('EMERGENCY', $payload['type_medical_summary']);
    }

    public function testBuildPayloadMissingFieldThrowsException(): void
    {
        $ssrme = $this->createMockSSRME();

        $this->expectException(SSRMEException::class);
        $this->expectExceptionMessage('patient_id');

        $ssrme->buildPayload([
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);
    }

    public function testFluentInterface(): void
    {
        $ssrme = $this->createMockSSRME();
        $ssrme->setPatient('P111', 'Budi')
            ->setPractitioner('N222', 'dr. Sinta')
            ->setOrganization('ORG333', 'Klinik Pratama')
            ->setEmergency(true);

        $payload = $ssrme->buildPayload();

        $this->assertSame('P111', $payload['patient_id']);
        $this->assertSame('Budi', $payload['patient_name']);
        $this->assertSame('N222', $payload['practitioner_id']);
        $this->assertSame('dr. Sinta', $payload['practitioner_name']);
        $this->assertSame('ORG333', $payload['organization_id']);
        $this->assertSame('Klinik Pratama', $payload['organization_name']);
        $this->assertSame('EMERGENCY', $payload['type_medical_summary']);
    }

    public function testCreateCHLinkSuccess(): void
    {
        $mockResponseBody = [
            'status' => 'success',
            'data' => [
                'url' => 'https://satusehat.kemkes.go.id/consent/verify/xyz123',
            ],
        ];

        $ssrme = $this->createMockSSRME([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponseBody)),
        ]);

        $response = $ssrme->createCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame(200, $response->getHttpCode());
        $this->assertSame('https://satusehat.kemkes.go.id/consent/verify/xyz123', $response->json()['data']['url']);
    }

    public function testCreateEmergencyCHLinkSuccess(): void
    {
        $mockResponseBody = [
            'status' => 'success',
            'message' => 'Emergency bypass consent recorded',
        ];

        $ssrme = $this->createMockSSRME([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponseBody)),
        ]);

        $response = $ssrme->createEmergencyCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame(200, $response->getHttpCode());
    }

    public function testCreateSHLinkSuccess(): void
    {
        $mockResponseBody = [
            'status' => 'success',
            'data' => [
                'shlink_url' => 'https://satusehat.kemkes.go.id/rme/viewer/shl-abc456',
            ],
        ];

        $ssrme = $this->createMockSSRME([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponseBody)),
        ]);

        $response = $ssrme->createSHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);

        $this->assertTrue($response->isSuccess());
        $this->assertSame('https://satusehat.kemkes.go.id/rme/viewer/shl-abc456', $response->json()['data']['shlink_url']);
    }

    public function testDiagnostic401InvalidToken(): void
    {
        $errorBody = [
            'statusCode' => 401,
            'message' => 'Invalid Access Token',
        ];

        $ssrme = $this->createMockSSRME([
            new Response(401, ['Content-Type' => 'application/json'], json_encode($errorBody)),
        ]);

        $this->expectException(SSRMEException::class);
        $this->expectExceptionCode(401);
        $this->expectExceptionMessage('Invalid Access Token');

        $ssrme->createCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);
    }

    public function testDiagnostic400WrongOrganization(): void
    {
        $errorBody = [
            'statusCode' => 400,
            'message' => 'wrong organization',
        ];

        $ssrme = $this->createMockSSRME([
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorBody)),
        ]);

        $this->expectException(SSRMEException::class);
        $this->expectExceptionMessage('Wrong Organization');

        $ssrme->createCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);
    }

    public function testDiagnostic400PatientNotFound(): void
    {
        $errorBody = [
            'statusCode' => 400,
            'message' => 'patient with ID P20066076728 not found',
        ];

        $ssrme = $this->createMockSSRME([
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorBody)),
        ]);

        $this->expectException(SSRMEException::class);
        $this->expectExceptionMessage('Patient Not Found');

        $ssrme->createCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);
    }

    public function testDiagnostic400PractitionerNotFound(): void
    {
        $errorBody = [
            'statusCode' => 400,
            'message' => 'practitioner with ID 10006000010 not found',
        ];

        $ssrme = $this->createMockSSRME([
            new Response(400, ['Content-Type' => 'application/json'], json_encode($errorBody)),
        ]);

        $this->expectException(SSRMEException::class);
        $this->expectExceptionMessage('Practitioner Not Found');

        $ssrme->createCHLink([
            'patient_id' => 'P20066076728',
            'patient_name' => 'Tn. Asep Mulyono',
            'practitioner_id' => '10006000010',
            'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
        ]);
    }

    public function testRenderButtonHtml(): void
    {
        $darkBtn = SSRME::renderButton('https://example.com/consent', 'dark');
        $this->assertStringContainsString('https://example.com/consent', $darkBtn);
        $this->assertStringContainsString('SATUSEHAT', $darkBtn);
        $this->assertStringContainsString('#0f172a', $darkBtn);

        $lightBtn = SSRME::renderButton('https://example.com/viewer', 'light');
        $this->assertStringContainsString('#ffffff', $lightBtn);

        $floatingBtn = SSRME::renderButton('https://example.com', 'dark-color', ['floating' => true]);
        $this->assertStringContainsString('position:fixed', $floatingBtn);
        $this->assertStringContainsString('#004d40', $floatingBtn);
    }
}
