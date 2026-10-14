<?php

declare(strict_types=1);

namespace Newus\Satusehat;

use GuzzleHttp\Psr7\Request;
use Newus\Satusehat\Exception\Helper\OAuth2ClientException;
use Newus\Satusehat\Exception\Helper\SSRMEException;
use Newus\Satusehat\Http\HttpClientFactory;
use Newus\Satusehat\SSResponse\SSResponse;

/**
 * SSRME (SATUSEHAT Rekam Medis Elektronik)
 *
 * Mengelola integrasi Modul Rekam Medis Elektronik Nasional:
 * 1. API CHLink (Consent Health Link) — Persetujuan Pasien di Pendaftaran/Perawat & Emergency Bypass
 * 2. API SHLink (Smart Health Link) — Akses RME Pasien Nasional oleh Dokter
 * 3. Helper Komponen Tombol Akses SATUSEHAT RME (Dark, Dark-Color, Light)
 */
class SSRME extends OAuth2Client
{
    private string $chlEndpoint = '/ssrme/v2/rct/chl';

    private string $shlEndpoint = '/ssrme/v2/rct/shl';

    private ?string $patientId = null;

    private ?string $patientName = null;

    private ?string $practitionerId = null;

    private ?string $practitionerName = null;

    private bool $isEmergency = false;

    /**
     * @param array<string, mixed>|OAuth2Client $configOrClient
     */
    public function __construct($configOrClient = [])
    {
        if ($configOrClient instanceof OAuth2Client) {
            parent::__construct([
                'override' => true,
                'environment' => $configOrClient->satusehat_env,
            ]);

            $this->base_url = $configOrClient->base_url;
            $this->client_id = $configOrClient->client_id;
            $this->client_secret = $configOrClient->client_secret;
            $this->organization_id = $configOrClient->organization_id;
            $this->organization_name = $configOrClient->organization_name;
            $this->satusehat_env = $configOrClient->satusehat_env;
            $this->auth_url = $configOrClient->auth_url;
            $this->fhir_url = $configOrClient->fhir_url;
        } else {
            parent::__construct(is_array($configOrClient) ? $configOrClient : []);
        }

        if (is_array($configOrClient)) {
            if (! empty($configOrClient['chl_endpoint'])) {
                $this->chlEndpoint = $configOrClient['chl_endpoint'];
            }
            if (! empty($configOrClient['shl_endpoint'])) {
                $this->shlEndpoint = $configOrClient['shl_endpoint'];
            }
        }
    }

    /**
     * Get Base URL for SSRME endpoints.
     */
    public function getSsrmeBaseUrl(): string
    {
        $customBase = getenv('SATUSEHAT_SSRME_BASE_URL');
        if (! empty($customBase)) {
            return rtrim($customBase, '/');
        }

        return rtrim((string) $this->base_url, '/');
    }

    /**
     * Get full CHLink endpoint URL.
     */
    public function getChlinkUrl(): string
    {
        return $this->getSsrmeBaseUrl() . $this->chlEndpoint;
    }

    /**
     * Get full SHLink endpoint URL.
     */
    public function getShlinkUrl(): string
    {
        return $this->getSsrmeBaseUrl() . $this->shlEndpoint;
    }

    /**
     * Set patient info fluently.
     */
    public function setPatient(string $id, string $name): self
    {
        $this->patientId = trim($id);
        $this->patientName = trim($name);
        return $this;
    }

    /**
     * Set practitioner / dokter info fluently.
     */
    public function setPractitioner(string $id, string $name): self
    {
        $this->practitionerId = trim($id);
        $this->practitionerName = trim($name);
        return $this;
    }

    /**
     * Set organization / fasyankes info fluently.
     */
    public function setOrganization(string $id, ?string $name = null): self
    {
        $this->organization_id = trim($id);
        if ($name !== null) {
            $this->organization_name = trim($name);
        }
        return $this;
    }

    /**
     * Set emergency condition flag (Bypass consent).
     */
    public function setEmergency(bool $isEmergency = true): self
    {
        $this->isEmergency = $isEmergency;
        return $this;
    }

    /**
     * Reset fluent state.
     */
    public function reset(): self
    {
        $this->patientId = null;
        $this->patientName = null;
        $this->practitionerId = null;
        $this->practitionerName = null;
        $this->isEmergency = false;
        return $this;
    }

    /**
     * Buat link persetujuan pasien (API CHLink) di Meja Registrasi / Pendaftaran / Stasiun Perawat.
     *
     * @param array<string, mixed> $data
     * @return SSResponse
     * @throws SSRMEException
     */
    public function createCHLink(array $data = []): SSResponse
    {
        $payload = $this->buildPayload($data, true);
        return $this->sendSsrmeRequest($this->getChlinkUrl(), $payload, 'POST CHLink');
    }

    /**
     * Buat link CHLink untuk kondisi gawat darurat (Emergency Bypass Consent).
     *
     * @param array<string, mixed> $data
     * @return SSResponse
     * @throws SSRMEException
     */
    public function createEmergencyCHLink(array $data = []): SSResponse
    {
        $data['type_medical_summary'] = 'EMERGENCY';
        return $this->createCHLink($data);
    }

    /**
     * Buat Smart Health Link (API SHLink) untuk dokter membuka RME Nasional di Ruang Pemeriksaan.
     *
     * @param array<string, mixed> $data
     * @return SSResponse
     * @throws SSRMEException
     */
    public function createSHLink(array $data = []): SSResponse
    {
        $payload = $this->buildPayload($data, false);
        return $this->sendSsrmeRequest($this->getShlinkUrl(), $payload, 'POST SHLink');
    }

    /**
     * Validasi dan rangkai payload SSRME.
     *
     * @param array<string, mixed> $data
     * @param bool $allowEmergency
     * @return array<string, mixed>
     * @throws SSRMEException
     */
    public function buildPayload(array $data = [], bool $allowEmergency = true): array
    {
        $patientId = $data['patient_id'] ?? $this->patientId;
        $patientName = $data['patient_name'] ?? $this->patientName;
        $practitionerId = $data['practitioner_id'] ?? $this->practitionerId;
        $practitionerName = $data['practitioner_name'] ?? $this->practitionerName;
        $orgId = $data['organization_id'] ?? $this->organization_id;
        $orgName = $data['organization_name'] ?? $this->organization_name;

        if (empty($patientId)) {
            throw SSRMEException::missingField('patient_id');
        }
        if (empty($patientName)) {
            throw SSRMEException::missingField('patient_name');
        }
        if (empty($practitionerId)) {
            throw SSRMEException::missingField('practitioner_id');
        }
        if (empty($practitionerName)) {
            throw SSRMEException::missingField('practitioner_name');
        }
        if (empty($orgId)) {
            throw SSRMEException::missingField('organization_id');
        }
        if (empty($orgName)) {
            throw SSRMEException::missingField('organization_name');
        }

        $payload = [
            'patient_id' => (string) $patientId,
            'patient_name' => (string) $patientName,
            'practitioner_id' => (string) $practitionerId,
            'practitioner_name' => (string) $practitionerName,
            'organization_id' => (string) $orgId,
            'organization_name' => (string) $orgName,
        ];

        if ($allowEmergency) {
            $isEmergency = false;
            if (isset($data['type_medical_summary']) && strtoupper((string) $data['type_medical_summary']) === 'EMERGENCY') {
                $isEmergency = true;
            } elseif (! empty($data['emergency']) || $this->isEmergency) {
                $isEmergency = true;
            }

            if ($isEmergency) {
                $payload['type_medical_summary'] = 'EMERGENCY';
            }
        }

        return $payload;
    }

    /**
     * Kirim request ke endpoint SSRME dengan OAuth2 Bearer token otomatis.
     *
     * @param string $url
     * @param array<string, mixed> $payload
     * @param string $actionName
     * @return SSResponse
     * @throws SSRMEException
     */
    private function sendSsrmeRequest(string $url, array $payload, string $actionName): SSResponse
    {
        $token = $this->token();
        if (is_array($token) || empty($token)) {
            throw SSRMEException::invalidAccessToken('Gagal mendapatkan access token OAuth2 dari SATUSEHAT.');
        }

        $client = HttpClientFactory::getClient();
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $jsonPayload = json_encode($payload);
        $request = new Request('POST', $url, $headers, $jsonPayload);

        try {
            $response = $client->send($request);
            $statusCode = $response->getStatusCode();
            $rawContent = (string) $response->getBody();
            $body = json_decode($rawContent, true);

            if (! is_array($body)) {
                $body = ['raw' => $rawContent];
            }

            $this->log($actionName, 'POST', $url, (array) $payload, $body);

            $ssResponse = new SSResponse($statusCode, $body, $rawContent);

            if ($statusCode >= 400) {
                $this->handleErrorDiagnostics($statusCode, $body, $rawContent, $payload);
            }

            return $ssResponse;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $res = $e->getResponse();
            $statusCode = $res ? $res->getStatusCode() : 400;
            $rawContent = $res ? (string) $res->getBody() : $e->getMessage();
            $body = json_decode($rawContent, true) ?: ['error' => $rawContent];

            $this->log($actionName . ' Error', 'POST', $url, (array) $payload, $body);
            $this->handleErrorDiagnostics($statusCode, $body, $rawContent, $payload);

            return new SSResponse($statusCode, $body, $rawContent);
        }
    }

    /**
     * Menganalisis respon error dan memberikan pesan diagnostik spesifik sesuai petunjuk Kemenkes.
     *
     * @param int $statusCode
     * @param array<string, mixed> $body
     * @param string $rawContent
     * @param array<string, mixed> $payload
     * @throws SSRMEException
     */
    private function handleErrorDiagnostics(int $statusCode, array $body, string $rawContent, array $payload): void
    {
        $errorText = strtolower($rawContent);

        if ($statusCode === 401 || strpos($errorText, 'invalid access token') !== false) {
            throw SSRMEException::invalidAccessToken($rawContent);
        }

        if (strpos($errorText, 'wrong organization') !== false) {
            throw SSRMEException::wrongOrganization($payload['organization_id'] ?? '', $rawContent);
        }

        if (strpos($errorText, 'patient with id') !== false && strpos($errorText, 'not found') !== false) {
            throw SSRMEException::patientNotFound($payload['patient_id'] ?? '', $rawContent);
        }

        if (strpos($errorText, 'practitioner with id') !== false && strpos($errorText, 'not found') !== false) {
            throw SSRMEException::practitionerNotFound($payload['practitioner_id'] ?? '', $rawContent);
        }
    }

    /**
     * Render Tombol Akses SATUSEHAT Rekam Medis (Modul 1 Kemenkes).
     *
     * @param string $url Target URL (CHLink consent URL atau SHLink RME viewer)
     * @param string $theme 'dark' | 'dark-color' | 'light'
     * @param array<string, mixed> $options ['floating' => bool, 'class' => string, 'label' => string]
     * @return string
     */
    public static function renderButton(string $url, string $theme = 'dark', array $options = []): string
    {
        $isFloating = $options['floating'] ?? false;
        $customClass = $options['class'] ?? '';
        $label = $options['label'] ?? 'SATUSEHAT<br><small style="font-weight:normal;font-size:0.8em;opacity:0.9;">Rekam Medis</small>';

        // Color palettes based on Kemenkes SSRME guidelines
        if ($theme === 'light') {
            $bgColor = '#ffffff';
            $textColor = '#0f172a';
            $borderColor = '#cbd5e1';
            $iconColor = '#00b4a7';
        } elseif ($theme === 'dark-color') {
            $bgColor = '#004d40';
            $textColor = '#ffffff';
            $borderColor = '#00695c';
            $iconColor = '#4db6ac';
        } else {
            // default: dark
            $bgColor = '#0f172a';
            $textColor = '#ffffff';
            $borderColor = '#1e293b';
            $iconColor = '#38bdf8';
        }

        $floatingStyle = $isFloating
            ? 'position:fixed;bottom:24px;right:24px;z-index:9999;box-shadow:0 10px 25px -5px rgba(0,0,0,0.3);'
            : '';

        $logoSvg = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="' . $iconColor . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-right:10px;flex-shrink:0;"><path d="M12 2v20M2 12h20"/><circle cx="12" cy="12" r="10"/></svg>';
        $arrowSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:8px;opacity:0.8;"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>';

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" class="satusehat-ssrme-btn ' . htmlspecialchars($customClass, ENT_QUOTES, 'UTF-8') . '" style="' . $floatingStyle . 'display:inline-flex;align-items:center;padding:10px 18px;border-radius:9999px;background-color:' . $bgColor . ';color:' . $textColor . ';border:1px solid ' . $borderColor . ';text-decoration:none;font-family:system-ui,-apple-system,sans-serif;font-weight:600;font-size:14px;line-height:1.2;transition:transform 0.15s ease,box-shadow 0.15s ease;cursor:pointer;">' . $logoSvg . '<span style="text-align:left;">' . $label . '</span>' . $arrowSvg . '</a>';
    }
}
