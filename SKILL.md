---
name: dokter-v-contract-bast-agent
description: REST API skill untuk pembuatan alokasi honor mitra, penerbitan kontrak/SPK, dan BAST otomatis di Dokter V BPS dengan optimasi efisiensi token AI.
version: 1.4.0
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
                 ├─── [Auto-Create]: Jika kegiatan/honor belum terdaftar:
                 │    └──► POST /api/v1/kegiatan-manmit (Buat master kegiatan + pos honor atomik)
                 │    └──► POST /api/v1/honors (Buat pos honor di bawah kegiatan induk)
                 ▼
[Step 2: Lookup Mitra Tersedia]       ──► GET /api/v1/mitras?tahun=2026&bulan=3&available_only=1&compact=1
                 │
[Step 3: Pre-Flight Check]             ──► POST /api/v1/alokasi/check (Dry-Run: Cek bentrok & sisa SBML)
                 │ (Jika eligible: true)
                 ▼
[Step 4: Eksekusi Alokasi / Update]   ──► POST /api/v1/alokasi (Atomik: Generate SPK & BAST)
                 │                    ──► PATCH /api/v1/alokasi/{id} (Update volume & revalidasi)
                 ▼
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
- `POST /api/v1/kegiatan-manmit`: Daftarkan Master Kegiatan Manmit baru dari nol (bisa menyertakan pos honor awal dalam array `honors` secara atomik). Reversible via rollback!
  - Body: `{"id": "DPP26", "nama": "(DPP26) Updating DPP 2026", "tgl_mulai_pelaksanaan": "2026-07-01", "tgl_akhir_pelaksanaan": "2026-09-30", "jenis_kegiatan": "SURVEI", "honors": [{"jabatan": "PPL", "jenis_honor": "PENDATAAN", "satuan_honor": "DOKUMEN", "harga_per_satuan": 53000, "tanggal_akhir_kegiatan": "2026-09-30"}]}`
- `DELETE /api/v1/kegiatan-manmit/{id}`: Hapus kegiatan jika belum ada alokasi mitra (`alokasi_honors_count == 0`). Reversible via rollback!
- `GET /api/v1/kegiatan-manmit/{id}`: Detail satu kegiatan dan seluruh rincian honor anak.
- `PATCH /api/v1/kegiatan-manmit/{id}`: Perpanjang/ubah rentang jadwal kegiatan (`tgl_mulai_pelaksanaan`, `tgl_akhir_pelaksanaan`), nama kegiatan, tipe (`jenis_kegiatan`: `SURVEI`/`SENSUS`), frekuensi (`frekuensi_kegiatan`: `SUBROUND`, `TAHUNAN`, `TRIWULANAN`, `BULANAN`, `SEMESTERAN`, `ADHOC`, `PERIODIK`), atau `template_kontrak`. Reversible via audit log rollback.
- `POST /api/v1/kegiatan-manmit/{id}/rename-id` (Rename / Migrasi ID Kegiatan):
  - Body: `{"new_id": "SERUTI26-TW3", "new_nama": "Opsional Nama Baru", "cascade_honor_ids": true}`
  - Fitur: Migrasi PK ID kegiatan secara atomik dan meng-cascade referensi di `honors`, `alokasi_honors`, dan `kegiatans`. 100% aman menjaga keutuhan nomor SPK dan BAST tanpa mereset nomor surat!

### B. Manajemen Master Honor & Penyesuaian Tanggal
- `GET /api/v1/honors?kegiatan_id=SERUTI26-TW3&compact=1`
- Query Params: `q`, `kegiatan_id`, `tahun`, `bulan`, `jabatan`, `compact`, `sort_by`, `sort_order`, `per_page`.
- `POST /api/v1/honors`: Daftarkan Master Pos Honor baru di bawah kegiatan yang sudah ada. Reversible via rollback!
  - Body: `{"kegiatan_manmit_id": "DUTL26", "jabatan": "PPL", "jenis_honor": "PENDATAAN", "satuan_honor": "DOKUMEN", "harga_per_satuan": 53000, "tanggal_akhir_kegiatan": "2026-09-30"}`
- `DELETE /api/v1/honors/{id}`: Hapus pos honor jika belum ada alokasi mitra. Reversible via rollback!
- `GET /api/v1/honors/{id}`: Detail satu entitas honor beserta relasi kegiatan induk dan jumlah alokasi terkait.
- `PATCH /api/v1/honors/{id}` (atau `PUT` / `POST`): Update atribut honor (`tanggal_akhir_kegiatan`, `harga_per_satuan`, `satuan_honor`, `jabatan`, `jenis_honor`).
  - Fitur: Memvalidasi rentang tanggal terhadap kegiatan induk, otomatis menghitung `tanggal_pembayaran_maksimal` (+20 hari), dan **memicu propagasi otomatis** ke seluruh alokasi honor dan nomor SPK/BAST terkait via `HonorTanggalService`. Reversible via rollback!
- **SOP 2 Langkah Perpanjangan Tanggal Honor**:
  1. *Langkah 1*: Jika rentang kegiatan utama belum mencakup tanggal baru, perpanjang kegiatan via `PATCH /api/v1/kegiatan-manmit/{id}` (`{"tgl_akhir_pelaksanaan": "YYYY-MM-DD"}`).
  2. *Langkah 2*: Sesuaikan tanggal honor via `PATCH /api/v1/honors/{id}` (`{"tanggal_akhir_kegiatan": "YYYY-MM-DD"}`).

### C. Lookup & Manajemen Mitra Statistik
- `GET /api/v1/mitras?tahun=2026&bulan=3&available_only=1&compact=1`
- Query Params: `q` (nama/NIK/Sobat/email/telp/WA), `ids` (comma-separated), `tahun`, `bulan`, `status` (AKTIF/dll), `aktif_only`, `kecamatan`, `desa`, `jenis_kelamin` (L/P), `posisi` (PCL/PML), `has_allocations` (0/1), `with_sbml`, `available_only`, `sort_by` (nama/id/sobat/nik/created_at), `sort_order` (asc/desc), `compact`, `per_page`.
- `GET /api/v1/mitras/{id}`: Detail profil mitra (identifikasi via ID, ID Sobat, atau NIK) lengkap dengan kontak, WhatsApp, dan riwayat kemitraan.
- `POST /api/v1/mitras`: Daftarkan mitra baru ke dalam sistem + binding status kemitraan tahunan. Reversible via rollback!
  - Body: `{"nama": "Herri Gustaman", "nik": "6104220200480001", "id_sobat": "610422020048", "nomor_wa": "081258309999", "status_kemitraan": "AKTIF", "tahun": 2026}`
- `PATCH /api/v1/mitras/{id}` (atau `PUT` / `POST`): Update informasi mitra.
  - Body: `{"nomor_wa": "081258306655", "no_telp": "...", "email": "...", "alamat_detail": "...", "catatan": "...", "status_kemitraan": "AKTIF", "tahun": 2026}`
  - Kolom Khusus: `nomor_wa` tersimpan di kolom fisik tersendiri (terpisah dari `no_telp` impor SOBAT), dinormalisasi otomatis, tercatat di `ApiAuditLog`, dan 100% reversible via rollback.
- `DELETE /api/v1/mitras/{id}`: Hapus data mitra jika belum memiliki alokasi honor aktif. Reversible via rollback!

### D. Lookup & Manajemen Pegawai BPS
- `GET /api/v1/pegawais?q=Ihza&compact=1`
- Query Params: `q`, `unit_kerja`, `jabatan`, `golongan`, `is_magang`, `compact`, `sort_by`, `sort_order`, `per_page`.
- `GET /api/v1/pegawais/{nip}`: Detail satu pegawai (bisa lookup via 18-digit NIP atau 9-digit NIP9) dan relasi atasan langsung.
- `POST /api/v1/pegawais`: Daftarkan pegawai baru. Auto-normalisasi nomor WA (`628xxx`), tercatat di audit log, dan reversible (rollback akan menghapus pegawai baru tersebut).
- `PATCH /api/v1/pegawais/{nip}`: Update data pegawai (jabatan, nomor WA, unit kerja, email, pangkat, golongan, dll). Reversible via rollback.
- `DELETE /api/v1/pegawais/{nip}`: Hapus pegawai. Reversible via rollback!

### E. Pre-Flight Check (Dry Run)
- `POST /api/v1/alokasi/check`
- Body: `{"honor_id": "HON-1", "mitra_id": 123, "target": 10.0}`
- Response: `{ "status": "success", "data": { "eligible": true|false, "reason": "...", ... } }`

### F. Buat & Kelola Alokasi (Single / Batch / Update)
- `POST /api/v1/alokasi`: Buat alokasi single atau batch (`allocations: [...]`).
- `PATCH /api/v1/alokasi/{id}`: Perbarui target volume alokasi, posisi honor, tanggal perjanjian, atau status.
  - Body: `{"target": 15, "honor_id": "HON-2026-002", "status": "APPROVED"}`
  - Fitur: Otomatis validasi ulang pagu SBML bulanan & bentrok sensus, hitung ulang total honor, tercatat di `ApiAuditLog` (`action: UPDATE_ALOKASI`), dan 100% reversible via rollback!
- `DELETE /api/v1/alokasi/{id}`: Hapus alokasi honor. Reversible via rollback!

### G. Dokumen Kontrak (SPK) & BAST
- `GET /api/v1/kontrak?tahun=2026&bulan=3&compact=1`
- `GET /api/v1/bast?tahun=2026&bulan=3&compact=1`
- `PATCH /api/v1/kontrak/{id}`: Update tanggal penerbitan SPK atau penomoran. Otomatis sinkronisasi ke tanggal penandatanganan seluruh alokasi terkait. Reversible via rollback!
- `PATCH /api/v1/bast/{id}`: Update tanggal penandatanganan BAST atau penomoran. Reversible via rollback!
- Query Params: `q`, `mitra_id`, `id_sobat`, `kegiatan_id`, `tahun`, `bulan`, `compact`, `page`, `per_page`.
- **Kaidah URL Cetak Dokumen (SOP URL Cetak)**:
  - **SPK Bulanan**: Format tautan resmi adalah `https://<domain>/cetak/kontrak?tahun={tahun}&bulan={bulan}&mitra_id={mitra_id}` (TANPA parameter `id_kegiatan_manmit`, agar semua lampiran kegiatan survei mitra di bulan kalender tersebut terkonsolidasi penuh).
  - **BAST**: Format tautan menyertakan `id_kegiatan_manmit` (`https://<domain>/cetak/bast?tahun={tahun}&bulan={bulan}&id_kegiatan_manmit={id_kegiatan}&mitra_id={mitra_id}`) karena BAST bersifat spesifik per alokasi/kegiatan.

### H. Audit Log & Self-Correction Rollback
- `GET /api/v1/audit-logs?only_rollbackable=1&compact=1`: Temukan mutasi yang bisa dibatalkan.
- `GET /api/v1/audit-logs/{id}`: Detail state diff sebelum dan sesudah.
- `POST /api/v1/audit-logs/{id}/rollback`: Batalkan perubahan secara atomik.
- **Aksi yang Didukung Rollback 100%**: `CREATE_KEGIATAN_MANMIT`, `DELETE_KEGIATAN_MANMIT`, `UPDATE_KEGIATAN_MANMIT`, `RENAME_KEGIATAN_ID`, `CREATE_HONOR`, `DELETE_HONOR`, `UPDATE_HONOR`, `CREATE_MITRA`, `UPDATE_MITRA`, `DELETE_MITRA`, `CREATE_PEGAWAI`, `UPDATE_PEGAWAI`, `DELETE_PEGAWAI`, `ALLOCATE_HONOR`, `BATCH_ALLOCATE`, `UPDATE_ALOKASI`, `DELETE_ALLOCATION`, `UPDATE_KONTRAK`, `UPDATE_BAST`.
