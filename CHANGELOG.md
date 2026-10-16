# Changelog

Semua perubahan penting pada package ini akan didokumentasikan dalam berkas ini. Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.3] - 2026-10-16

### Changed
- **Environment Simplification**: Menghapus environment `DEV` yang tidak digunakan dan hanya mendukung 2 jenis environment resmi SATUSEHAT: `STG` (Sandbox/Staging) dan `PROD` (Production).
- **Environment Aliasing**: Otomatis menormalisasi nilai `SANDBOX` dan `STAGING` ke `STG` serta `PRODUCTION` ke `PROD`.
- **Environment Fallback Credentials**: Mendukung resolusi variabel environment `CLIENTID_SANDBOX`, `CLIENTSECRET_SANDBOX`, dan `ORGID_SANDBOX` sebagai fallback untuk `_STG`.
- **Documentation & Examples**: Memperbarui `.env.example` dan `README.md` dengan panduan konfigurasi environment `STG` dan `PROD`.

---

## [1.0.2] - 2026-10-15

### Added
- **Sandbox Dummy Datasets**: Dataset lengkap 10 pasien dummy dan 10 tenaga kesehatan/dokter dummy resmi SATUSEHAT Sandbox DTO Kemenkes RI.
- **Lookup Helpers**: Method pencarian dummy sandbox berdasarkan NIK (`findPatientSandboxByNik`, `findPractitionerSandboxByNik`) dan Nomor IHS (`findPatientSandboxByIhs`, `findPractitionerSandboxByIhs`).
- **Patient Search Helpers**: Method helper fluent untuk pencarian pasien berdasarkan NIK (`getPatientByNik`), NIK Ibu (`getPatientByNikIbu`), dan parameter lainnya.

---

## [1.0.1] - 2026-10-14

### Added
- **Modul SSRME (SATUSEHAT Rekam Medis Elektronik)**: Integrasi modul resmi DTO Kemenkes untuk akses resume medis pasien nasional melalui API CHLink (`createCHLink`) dan SHLink (`createSHLink`).

---

## [1.0.0] - 2026-10-13

### Rilis Awal — Newus Technology (`newustechnology/satusehat-integration`)

#### Added
- **Framework-Agnostic Core**: Dukungan penuh untuk **CodeIgniter 3**, **CodeIgniter 4**, **Laravel (8.x - 13.x)**, dan **Native PHP**.
- **Multi-Tier Token Caching**:
  - **L1 Cache (RAM In-Memory)**: Akses token instan (0ms) untuk pemanggilan berulang dalam 1 lifecycle request/queue worker.
  - **L2 Cache (Storage Adapter)**: Penyimpanan persisten via `FileTokenStorage` (otomatis untuk CodeIgniter & Native) dan `LaravelCacheTokenStorage` / `EloquentTokenStorage` (untuk Laravel).
- **HTTP Connection Pooling & Keep-Alive**: `HttpClientFactory` singleton terintegrasi dengan Guzzle keep-alive untuk efisiensi koneksi jaringan ke server SATUSEHAT Kemenkes.
- **Dukungan PHP 7.4 s/d 8.4**: Memastikan kompatibilitas penuh dengan sistem SIMRS / Klinik warisan berbasis CodeIgniter 3 di server PHP 7.4 tanpa memicu fatal error.
- **Static Dotenv Guard**: Mencegah disk I/O membaca file `.env` berulang kali saat membuat instance `OAuth2Client`.
- **43+ FHIR Resource Payload Builders**: Mendukung Patient, Encounter, Condition, DiagnosticReport, Observation, Procedure, Medication, Specimen, ServiceRequest, CarePlan, Immunization, FamilyMemberHistory, dll.
- **Fluent HTTP SSRequest Client**: Method chainable `get()`, `post()`, `put()`, `patch()`, `delete()` dengan auto-retry pada status HTTP 429 / 401.
