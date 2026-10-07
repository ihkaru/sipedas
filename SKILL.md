---
name: dokter-v-contract-bast-agent
description: REST API skill untuk pembuatan alokasi honor mitra, penerbitan kontrak/SPK, dan BAST otomatis di Dokter V BPS dengan optimasi efisiensi token AI.
version: 1.2.0
auth_header: X-API-KEY <your_api_key>
---

# Dokter V Contract & BAST Agent Protocol (October 2026 Edition)

Skill ini memberikan instruksi lengkap bagi AI Coding Agent untuk berinteraksi dengan REST API Dokter V secara deterministik, presisi, dan hemat token.

---

## 1. Aturan Bisnis & Validasi Ketat (Mandatory Constraints)
1. **Status Kemitraan Aktif**: Mitra HARUS berstatus `AKTIF` pada tahun kegiatan yang dialokasikan (`kemitraans.tahun == tahun_kegiatan`).
2. **Larangan Bentrok Jadwal Sensus (Strict Overlap)**:
   - Jika kegiatan bertipe `SENSUS`: tanggal kontrak sama sekali TIDAK BOLEH beririsan dengan kontrak lain di bulan yang sama.
   - Jika kedua kegiatan bertipe `SURVEI`: diperbolehkan beririsan tanggal, namun dibatasi oleh limit pagu SBML.
3. **Standar Biaya Masukan Lainnya (SBML Bulanan)**:
   - Pagu SBML Sensus: Rp 4.694.000 / bulan.
   - Pagu SBML Survei: Rp 3.353.000 / bulan.
   - Proporsi dihitung per hari kalender aktif di bulan target. Total akumulasi honor di setiap bulan terdampak tidak boleh melampaui limit.
4. **Target Volume**: Wajib berupa angka positif (`target > 0`).

---

## 2. Prinsip Efisiensi Token AI (Agent-Native Best Practices)
1. **Mode Compact (`?compact=1`)**: Selalu sertakan `compact=1` saat melakukan query `GET` untuk memangkas ukuran JSON payload hingga **80%**.
2. **Server-Side Filtering**:
   - Selalu filter berdasarkan bulan target: `?bulan=3`
   - Filter hanya kegiatan ber-honor: `?has_honors=1`
   - Filter mitra yang masih punya kuota SBML: `?available_only=1&with_sbml=1`
   - Gunakan batch ID lookup: `?ids=101,102,61041001` alih-alih request satu per satu.
3. **Pencarian Cepat**: Gunakan `?q={keyword}` untuk mencari spesifik nama, NIK, ID Sobat, kode kegiatan, atau nomor surat.

---

## 3. Alur Kerja Standar Agen (Recommended Workflow)

```
[Step 1: Lookup Kegiatan Hemat Token] ──► GET /api/v1/kegiatan-manmit?tahun=2026&bulan=3&has_honors=1&compact=1
                 │
[Step 2: Lookup Mitra Tersedia]       ──► GET /api/v1/mitras?tahun=2026&bulan=3&available_only=1&compact=1
                 │
[Step 3: Pre-Flight Check]             ──► POST /api/v1/alokasi/check (Dry-Run: Cek bentrok & sisa SBML)
                 │ (Jika eligible: true)
                 ▼
[Step 4: Eksekusi Alokasi]             ──► POST /api/v1/alokasi (Atomik: Generate SPK & BAST)
                 │
[Step 5: Verifikasi Dokumen]           ──► GET /api/v1/kontrak?bulan=3&compact=1 & GET /api/v1/bast?compact=1
                 │
[Step 6: Rollback jika Salah]          ──► GET /api/v1/audit-logs?only_rollbackable=1&compact=1
                                       ──► POST /api/v1/audit-logs/{id}/rollback
```

---

## 4. Spesifikasi Endpoint

### A. Lookup & Manajemen Kegiatan Manmit
- `GET /api/v1/kegiatan-manmit?tahun=2026&bulan=3&has_honors=1&compact=1`
- Query Params: `q`, `tahun`, `bulan`, `jenis` (SURVEI/SENSUS), `has_honors`, `compact`, `sort_by`, `sort_order`, `per_page`.
- `POST /api/v1/kegiatan-manmit/{id}/rename-id` (Rename / Migrasi ID Kegiatan):
  - Body: `{"new_id": "SERUTI26-TW3", "new_nama": "Opsional Nama Baru", "cascade_honor_ids": true}`
  - Fitur: Migrasi PK ID kegiatan secara atomik dan meng-cascade referensi di `honors`, `alokasi_honors`, dan `kegiatans`. 100% aman menjaga keutuhan nomor SPK dan BAST tanpa mereset nomor surat!

### B. Lookup & Manajemen Mitra Statistik
- `GET /api/v1/mitras?tahun=2026&bulan=3&available_only=1&compact=1`
- Query Params: `q` (nama/NIK/Sobat/email/telp/WA), `ids` (comma-separated), `tahun`, `bulan`, `status` (AKTIF/dll), `aktif_only`, `kecamatan`, `desa`, `jenis_kelamin` (L/P), `posisi` (PCL/PML), `has_allocations` (0/1), `with_sbml`, `available_only`, `sort_by` (nama/id/sobat/nik/created_at), `sort_order` (asc/desc), `compact`, `per_page`.
- `GET /api/v1/mitras/{id}`: Detail profil mitra (identifikasi via ID, ID Sobat, atau NIK) lengkap dengan kontak, WhatsApp, dan riwayat kemitraan.
- `PATCH /api/v1/mitras/{id}` (atau `PUT` / `POST`): Update informasi mitra.
  - Body: `{"nomor_wa": "081258306655", "no_telp": "...", "email": "...", "alamat_detail": "...", "catatan": "...", "status_kemitraan": "AKTIF", "tahun": 2026}`
  - Kolom Khusus: `nomor_wa` tersimpan di kolom fisik tersendiri (terpisah dari `no_telp` impor SOBAT), dinormalisasi otomatis, tercatat di `ApiAuditLog`, dan 100% reversible via rollback.

### C. Pre-Flight Check (Dry Run)
- `POST /api/v1/alokasi/check`
- Body: `{"honor_id": "HON-1", "mitra_id": 123, "target": 10.0}`
- Response: `{ "status": "success", "data": { "eligible": true|false, "reason": "...", ... } }`

### D. Buat Alokasi (Single / Batch)
- `POST /api/v1/alokasi`
- Single Body: `{"honor_id": "HON-1", "mitra_id": 123, "target": 10.0}`
- Batch Body: `{"allocations": [{"honor_id": "HON-1", "mitra_id": 123, "target": 10}]}`
- Response: ID alokasi, nomor SPK, nomor BAST, dan URL cetak PDF.

### E. Dokumen Kontrak & BAST
- `GET /api/v1/kontrak?tahun=2026&bulan=3&compact=1`
- `GET /api/v1/bast?tahun=2026&bulan=3&compact=1`
- Query Params: `q`, `mitra_id`, `id_sobat`, `kegiatan_id`, `tahun`, `bulan`, `compact`, `page`, `per_page`.
- **Kaidah URL Cetak Dokumen (SOP URL Cetak)**:
  - **SPK Bulanan**: Format tautan resmi adalah `https://<domain>/cetak/kontrak?tahun={tahun}&bulan={bulan}&mitra_id={mitra_id}` (TANPA parameter `id_kegiatan_manmit`, agar semua lampiran kegiatan survei mitra di bulan kalender tersebut terkonsolidasi penuh).
  - **BAST**: Format tautan menyertakan `id_kegiatan_manmit` (`https://<domain>/cetak/bast?tahun={tahun}&bulan={bulan}&id_kegiatan_manmit={id_kegiatan}&mitra_id={mitra_id}`) karena BAST bersifat spesifik per alokasi/kegiatan.

### F. Audit Log & Rollback
- `GET /api/v1/audit-logs?only_rollbackable=1&compact=1`: Temukan mutasi yang bisa dibatalkan.
- `GET /api/v1/audit-logs/{id}`: Detail state diff sebelum dan sesudah.
- `POST /api/v1/audit-logs/{id}/rollback`: Batalkan perubahan dan bersihkan nomor dokumen terkait secara atomik.
