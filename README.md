# SATUSEHAT Integration Package (PHP)

[![PHP Version](https://img.shields.io/badge/PHP-%5E7.4%20%7C%7C%20%5E8.0-blue)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)
[![Tests Passing](https://img.shields.io/badge/Tests-887%20passed-brightgreen.svg)](#)

Package PHP resmi dari **Newus Technology** untuk membangun objek FHIR R4 dan berintegrasi dengan API **SATUSEHAT Kementerian Kesehatan RI** secara mudah, cepat, dan berperforma tinggi.

Package ini dirancang **Framework-Agnostic**: dapat berjalan lancar di **CodeIgniter 3**, **CodeIgniter 4**, **Laravel (8.x s/d 13.x)**, maupun **PHP Native murni**.

---

## Fitur Utama

- **Framework-Agnostic Core**: Tidak mewajibkan Laravel. Dapat digunakan di CodeIgniter 3/4 maupun microservices vanilla PHP.
- **Multi-Tier Token Caching**:
  - **L1 In-Memory RAM**: 0 milidetik saat eksekusi berulang dalam 1 lifecycle request atau queue worker.
  - **L2 Persistent Storage**: Penyimpanan persisten otomatis berbasis file temporer atau Laravel Cache / database.
- **HTTP Connection Pooling (Guzzle Keep-Alive)**: Mengurangi latensi jaringan dan overhead TCP/TLS handshake ke server Kemenkes.
- **Zero Redundant Disk I/O**: Static guard untuk parsing `.env` sehingga aman dipanggil dalam batch loop.
- **Kompatibilitas Luas**: Mendukung **PHP ^7.4 dan ^8.0+** (sangat ramah untuk SIMRS / Klinik lama berbasis CI3 di PHP 7.4, serta otomatis kompatibel dengan versi PHP 8.x terbaru).
- **Lengkap dengan 43+ FHIR Builders**: Mendukung Patient, Encounter, Condition, DiagnosticReport, Observation, Procedure, Medication, Specimen, dll.

---

## Instalasi

Install package melalui Composer:

```bash
composer require newustechnology/satusehat-integration
```

---

## Konfigurasi Environment (`.env`)

```env
SATUSEHAT_ENV=STG # Opsi: STG (Sandbox/Staging), PROD (Production)

# Jika STG (Sandbox / Staging):
SATUSEHAT_BASE_URL_STG=https://api-satusehat-stg.dto.kemkes.go.id
CLIENTID_STG=your_stg_client_id
CLIENTSECRET_STG=your_stg_client_secret
ORGID_STG=your_stg_org_id

# Jika PROD:
SATUSEHAT_BASE_URL_PROD=https://api-satusehat.kemkes.go.id
CLIENTID_PROD=your_prod_client_id
CLIENTSECRET_PROD=your_prod_client_secret
ORGID_PROD=your_prod_org_id
```

---

## Panduan Penggunaan Berdasarkan Framework

### 1. CodeIgniter 3 (CI3)

Aktifkan composer autoload di `application/config/config.php`:
```php
$config['composer_autoload'] = TRUE; // atau FCPATH . 'vendor/autoload.php';
```

Contoh Controller (`application/controllers/Pasien.php`):
```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRequest\SSRequest;

class Pasien extends CI_Controller {

    public function cari_nik($nik)
    {
        // 1. Inisialisasi OAuth Client (bisa kirim parameter array langsung)
        $client = new OAuth2Client([
            'environment'     => 'STG',
            'client_id'       => 'your_client_id',
            'client_secret'   => 'your_client_secret',
            'organization_id' => 'your_org_id',
        ]);

        // 2. Request ke SATUSEHAT
        $request = new SSRequest($client);
        $response = $request->get('Patient', [
            'identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik
        ]);

        // 3. Tampilkan JSON response
        $this->output
            ->set_status_header($response->getHttpCode())
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response->json()));
    }
}
```

---

### 2. CodeIgniter 4 (CI4)

Contoh Controller (`app/Controllers/SatusehatController.php`):
```php
<?php

namespace App\Controllers;

use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRequest\SSRequest;

class SatusehatController extends BaseController
{
    public function getPatient(string $nik)
    {
        // Otomatis membaca konfigurasi dari file .env CI4
        $client = new OAuth2Client();
        $request = new SSRequest($client);

        $response = $request->get('Patient', [
            'identifier' => 'https://fhir.kemkes.go.id/id/nik|' . $nik
        ]);

        return $this->response->setJSON($response->json());
    }
}
```

---

### 3. Laravel (8.x s/d 13.x)

Package otomatis terdeteksi via Laravel Package Auto-Discovery.

Publish file konfigurasi & migrasi (opsional):
```bash
php artisan vendor:publish --provider="Newus\Satusehat\SatusehatServiceProvider"
```

Contoh di Controller atau Service:
```php
use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRequest\SSRequest;

class SatusehatController extends Controller
{
    public function index(OAuth2Client $client)
    {
        $request = new SSRequest($client);
        $response = $request->get('Organization/' . $client->organization_id);

        if ($response->isSuccess()) {
            return response()->json($response->json());
        }

        return response()->json($response->getErrorMessages(), 400);
    }
}
```

---

### 4. Native / Vanilla PHP

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Newus\Satusehat\OAuth2Client;
use Newus\Satusehat\SSRequest\SSRequest;

$client = new OAuth2Client([
    'environment' => 'STG',
    'client_id' => 'your_stg_id',
    'client_secret' => 'your_stg_secret',
    'organization_id' => 'your_org_id',
]);

$ss = new SSRequest($client);
$response = $ss->get('Patient', ['identifier' => 'https://fhir.kemkes.go.id/id/nik|1234567890123456']);

print_r($response->json());
```

---

## Modul Integrasi SSRME (SATUSEHAT Rekam Medis Elektronik)

Mendukung modul resmi DTO Kemenkes untuk akses resume rekam medis pasien nasional lintas fasyankes:

```php
use Newus\Satusehat\SSRME;

$ssrme = new SSRME();
```

### 1. API CHLink — Persetujuan Consent Pasien (Meja Registrasi / Perawat)
Dipanggil di Meja Pendaftaran / Registrasi / Stasiun Perawat untuk meminta persetujuan akses resume medis pasien:

```php
$response = $ssrme->createCHLink([
    'patient_id'        => 'P20066076728',
    'patient_name'      => 'Tn. Asep Mulyono',
    'practitioner_id'   => '10006000010',
    'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
    'organization_id'   => '10000004',      // Opsional jika sudah di .env
    'organization_name' => 'RS Satusehat',  // Opsional jika sudah di .env
]);

$consentUrl = $response->json()['data']['url'] ?? null;
```

### 2. Penanganan Kondisi Darurat (Emergency Bypass Consent)
Jika kondisi pasien gawat darurat, fasyankes dapat melakukan bypass persetujuan secara resmi:

```php
$response = $ssrme->createEmergencyCHLink([
    'patient_id'        => 'P20066076728',
    'patient_name'      => 'Tn. Asep Mulyono',
    'practitioner_id'   => '10006000010',
    'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
]);
```

### 3. API SHLink — Akses RME Pasien (Ruang Dokter)
Dipanggil saat dokter membuka modul resume rekam medis nasional pasien di SIMFASKES:

```php
$response = $ssrme->createSHLink([
    'patient_id'        => 'P20066076728',
    'patient_name'      => 'Tn. Asep Mulyono',
    'practitioner_id'   => '10006000010',
    'practitioner_name' => 'dr. Ratna Dewi, Sp.M',
]);

$rmeViewerUrl = $response->json()['data']['shlink_url'] ?? null;
```

### 4. Komponen Tombol Akses SATUSEHAT (Modul 1 Kemenkes)
Render tombol standar resmi Kemenkes (floating atau inline) dengan pilihan tema `dark`, `dark-color`, atau `light`:

```php
// Tombol Dark Floating di pojok kanan bawah
echo SSRME::renderButton($rmeViewerUrl, 'dark', ['floating' => true]);

// Tombol Light Inline
echo SSRME::renderButton($rmeViewerUrl, 'light');
```

---

## Contoh Pembuatan Resource FHIR (Payload Builder)

### Membuat Resource Patient
```php
use Newus\Satusehat\Builder\PayloadBuilderPatient;
use Newus\Satusehat\DataType\HumanName;
use Newus\Satusehat\DataType\Identifier;
use Newus\Satusehat\DataType\ContactPoint;

$patient = new PayloadBuilderPatient();
$patient->addIdentifier(new Identifier('https://fhir.kemkes.go.id/id/nik', '3171010101900001'))
    ->addName(new HumanName('Budi Santoso', 'official'))
    ->setGender('male')
    ->setBirthDate('1990-01-01')
    ->addTelecom(new ContactPoint('phone', '08123456789', 'mobile'));

$payload = $patient->build(); // Array valid FHIR R4
```

---

## Menjalankan Pengujian (Testing)

```bash
composer test
```

---

## Kontribusi & Keamanan

- **Keamanan**: Jika Anda menemukan kerentanan keamanan, silakan hubungi tim pengembang di [mnashiddiqi01@gmail.com](mailto:mnashiddiqi01@gmail.com).
- **Lisensi**: Dirilis di bawah [MIT License](LICENSE.md) © 2026 **Tnembull / Newus Technology**.
