<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SkillApiController extends Controller
{
    /**
     * Sajikan spesifikasi SKILL.md untuk AI Coding Agent.
     */
    public function show(Request $request): Response
    {
        $baseUrl = url('/api/v1');
        $format = $request->query('format', 'markdown');

        $markdownSkill = <<<MARKDOWN
---
name: sipedas-contract-bast-agent
description: REST API skill untuk pembuatan alokasi honor mitra, penerbitan kontrak/SPK, dan BAST otomatis di SIPEDAS BPS.
version: 1.0.0
base_url: {$baseUrl}
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
[Step 1: Lookup Kegiatan & Honor] ──► GET /kegiatan-manmit?tahun=2026
                │
[Step 2: Lookup Mitra Aktif]      ──► GET /mitras?tahun=2026&aktif_only=1
                │
[Step 3: Pre-Flight Check]        ──► POST /alokasi/check (Dry-Run: Cek bentrok & sisa SBML)
                │ (Jika eligible: true)
                ▼
[Step 4: Eksekusi Alokasi]        ──► POST /alokasi (Atomik: Generate SPK & BAST)
                │
[Step 5: Verifikasi Dokumen]      ──► GET /kontrak & GET /bast (Dapatkan link PDF cetak)
                │
[Step 6: Rollback jika Salah]     ──► POST /audit-logs/{id}/rollback (Kembalikan state)
```

## 3. Spesifikasi Endpoint

### A. Pre-Flight Check (Dry Run)
- **Method**: `POST`
- **Path**: `/alokasi/check`
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
- **Path**: `/alokasi`
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
- `GET /kontrak?tahun=2026&bulan=5&id_kegiatan_manmit=123`: Rekap Kontrak SPK dan link cetak PDF.
- `GET /bast?tahun=2026&bulan=5&id_kegiatan_manmit=123`: Rekap Dokumen BAST dan link cetak PDF.

### D. Audit Log & Rollback
- `GET /audit-logs`: Lihat riwayat perubahan yang dilakukan melalui API.
- `POST /audit-logs/{id}/rollback`: Batalkan perubahan sebelumnya dan pulihkan database ke state semula.
MARKDOWN;

        if ($format === 'json' || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'skill' => [
                    'name' => 'sipedas-contract-bast-agent',
                    'version' => '1.0.0',
                    'base_url' => $baseUrl,
                    'documentation_markdown' => $markdownSkill,
                    'endpoints' => [
                        'discovery' => [
                            'GET /kegiatan-manmit',
                            'GET /mitras',
                        ],
                        'allocation' => [
                            'POST /alokasi/check (Dry run)',
                            'POST /alokasi (Execute single/batch)',
                            'GET /alokasi',
                            'GET /alokasi/{id}',
                            'DELETE /alokasi/{id}',
                        ],
                        'documents' => [
                            'GET /kontrak',
                            'GET /bast',
                        ],
                        'audit_and_rollback' => [
                            'GET /audit-logs',
                            'POST /audit-logs/{id}/rollback',
                        ],
                    ],
                ],
            ]);
        }

        return response($markdownSkill, 200, [
            'Content-Type' => 'text/markdown; charset=UTF-8',
        ]);
    }
}
