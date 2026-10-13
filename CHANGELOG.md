# Changelog

Semua perubahan penting pada package ini akan didokumentasikan dalam berkas ini. Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.0.0/) dan mengikuti [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
