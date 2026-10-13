# Roadmap: Newus Technology SATUSEHAT Integration

> **Misi:** Membangun library integrasi SATUSEHAT FHIR R4 terlengkap, tercepat, dan paling fleksibel untuk ekosistem PHP Indonesia (CodeIgniter 3, CodeIgniter 4, Laravel, dan Native PHP).

---

## Timeline Versi

```
v1.0.0 (Rilis Saat Ini) ───  v1.1.0  ───  v1.2.0  ───  v2.0.0
       ✅                      🔲          🔲          🔲
Framework-Agnostic          Offline      High-Volume    FHIR R5 /
Core + CI3/CI4/Laravel      Terminology  Async Queue   Smart on FHIR
Multi-Tier Token Cache      Sync         Worker Pool   Features
```

---

## Rilis v1.0.0 (✅ Selesai)
- [x] **Framework-Agnostic Core**: Mendukung CodeIgniter 3, CodeIgniter 4, Laravel, dan Native PHP.
- [x] **Dukungan PHP 7.4 - 8.4**: Memastikan kompatibilitas penuh dengan sistem warisan tanpa fatal error.
- [x] **Multi-Tier Caching**: In-Memory L1 (0ms) + Storage Adapter L2 (File & Laravel Cache).
- [x] **HTTP Keep-Alive Connection Pooling**: Guzzle persistent connection pool.
- [x] **43+ FHIR Resource Builders**: Mendukung Patient, Encounter, Condition, DiagnosticReport, Observation, Procedure, dll.
- [x] **860+ Unit & Integration Tests**: Validasi menyeluruh seluruh payload dan client.

---

## Rencana v1.1.0 (Q1 2027)
- [ ] Offline Terminology SQLite Sync untuk pencarian ICD-10, ICD-9-CM, LOINC, dan KFA tanpa dependensi koneksi eksternal.
- [ ] CLI Assistant untuk validasi payload FHIR secara lokal sebelum dikirim ke Kemenkes.

---

## Rencana v1.2.0 (Q2 2027)
- [ ] High-volume async batch queue worker dengan auto-recovery pada rate limiting.
- [ ] Dukungan Webhook listener untuk notifikasi event SATUSEHAT.

---

## Hubungi Kami
- **Email:** mnashiddiqi01@gmail.com
- **Issues:** https://github.com/newustechnology/satusehat-integration/issues
