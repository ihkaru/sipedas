---
name: sipedas-contract-bast-agent
description: REST API skill untuk pembuatan alokasi honor mitra, penerbitan kontrak/SPK, dan BAST otomatis di SIPEDAS BPS.
version: 1.0.0
auth_header: X-API-KEY <your_api_key>
---

# SIPEDAS Contract & BAST Agent Protocol

Skill ini memberikan instruksi lengkap bagi AI Coding Agent untuk berinteraksi dengan REST API SIPEDAS secara deterministik dan aman.

## 1. Aturan Bisnis & Validasi Ketat (Mandatory Constraints)
Sebelum membuat alokasi, pahami 4 aturan validasi:
1. **Status Kemitraan Aktif**: Mitra HARUS berstatus `AKTIF` pada tahun kegiatan yang dialokasikan (`kemitraans.tahun == tahun_kegiatan`).
2. **Larangan Bentrok Jadwal Sensus (Strict Overlap)**:
   - Jika kegiatan bertipe `SENSUS`: tanggal kontrak sama sekali TIDAK BOLEH beririsan dengan kontrak lain di bulan yang sama.
   - Jika kedua kegiatan bertipe `SURVEI`: diperbolehkan beririsan tanggal, namun dibatasi oleh limit pagu SBML.
3. **Standar Biaya Masukan Lainnya (SBML Bulanan)**:
   - Pagu SBML Sensus: Rp 4.694.000 / bulan.
   - Pagu SBML Survei: Rp 3.353.000 / bulan.
   - Proporsi dihitung per hari kalender aktif di bulan target. Total akumulasi honor di setiap bulan terdampak tidak boleh melampaui limit.
4. **Target Volume**: Wajib berupa angka positif (`target > 0`).

## 2. Alur Kerja Standar Agen (Recommended Workflow)

```
[Step 1: Lookup Kegiatan & Honor] ──► GET /api/v1/kegiatan-manmit?tahun=2026
                │
[Step 2: Lookup Mitra Aktif]      ──► GET /api/v1/mitras?tahun=2026&aktif_only=1
                │
[Step 3: Pre-Flight Check]        ──► POST /api/v1/alokasi/check (Dry-Run: Cek bentrok & sisa SBML)
                │ (Jika eligible: true)
                ▼
[Step 4: Eksekusi Alokasi]        ──► POST /api/v1/alokasi (Atomik: Generate SPK & BAST)
                │
[Step 5: Verifikasi Dokumen]      ──► GET /api/v1/kontrak & GET /api/v1/bast (Dapatkan link PDF cetak)
                │
[Step 6: Rollback jika Salah]     ──► POST /api/v1/audit-logs/{id}/rollback (Kembalikan state)
```

## 3. Spesifikasi Endpoint

### A. Pre-Flight Check (Dry Run)
- **Method**: `POST`
- **Path**: `/api/v1/alokasi/check`
- **Headers**: `X-API-KEY: <key>`, `Content-Type: application/json`
- **Body**:
  ```json
  {
    "honor_id": "HONOR_ID",
    "mitra_id": 123,
    "target": 10.0
  }
  ```
- **Response**: `{ "status": "success", "data": { "eligible": true|false, "message": "...", "sisa_limit_sbml": {...} } }`

### B. Buat Alokasi (Single / Batch)
- **Method**: `POST`
- **Path**: `/api/v1/alokasi`
- **Single Body**:
  ```json
  {
    "honor_id": "HONOR_ID",
    "mitra_id": 123,
    "target": 10.0
  }
  ```
- **Batch Body**:
  ```json
  {
    "allocations": [
      { "honor_id": "HONOR_1", "mitra_id": 10, "target": 5 },
      { "honor_id": "HONOR_1", "id_sobat": "61040002", "target": 8 }
    ]
  }
  ```
- **Response (201 Created)**: Mengembalikan ID alokasi, detail nomor SPK, nomor BAST, dan URL cetak PDF.

### C. Dokumen Kontrak & BAST
- `GET /api/v1/kontrak?tahun=2026&bulan=5&id_kegiatan_manmit=123`: Rekap Kontrak SPK dan link cetak PDF.
- `GET /api/v1/bast?tahun=2026&bulan=5&id_kegiatan_manmit=123`: Rekap Dokumen BAST dan link cetak PDF.

### D. Audit Log & Rollback
- `GET /api/v1/audit-logs`: Lihat riwayat perubahan yang dilakukan melalui API.
- `POST /api/v1/audit-logs/{id}/rollback`: Batalkan perubahan sebelumnya dan pulihkan database ke state semula.
