# 11. Risiko dan Utang Teknis (Risks and Technical Debt)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memetakan matriks risiko arsitektur (termasuk risiko turunan dari ADR di Seksi 9) serta daftar utang teknis (*technical debt ledger*) yang wajib dikelola secara aktif.

---

## 11.1 Matriks Risiko Arsitektur (Architectural Risk Matrix)

| ID | Deskripsi Risiko | Dampak & Probabilitas | Strategi Mitigasi & Pengendalian |
|:---|:---|:---|:---|
| RISK-01 | Kebocoran Memori & Kontaminasi State Antar Request pada FrankenPHP Worker Mode | Dampak: Sedang<br>Probabilitas: Rendah | Menghindari penggunaan properti kelas statis (`static $state`) untuk menyimpan data per-request. Mengonfigurasi `OCTANE_MAX_REQUESTS=500` di Coolify agar worker di-recycle secara berkala untuk melepaskan memori yang tertahan. |
| RISK-02 | Inkonsistensi Data Historis Honor Sebelum Penegakan SSoT `tanggal_akhir_kegiatan` | Dampak: Tinggi<br>Probabilitas: Sedang | Menjalankan skrip migrasi data idempotent yang memverifikasi seluruh rekaman `honors` lama, mengisi `tanggal_akhir_kegiatan` dari tanggal kegiatan survei terkait, dan menandai anomali data untuk diverifikasi manual oleh admin satker. |
| RISK-03 | Latensi Jaringan atau Batas Kuota (Rate Limit) pada Upstream AI Gateway | Dampak: Sedang<br>Probabilitas: Sedang | Menerapkan timeout HTTP 15 detik pada `AIService`, penyediaan mekanisme retry eksponensial (maksimal 2x percobaan), serta fallback formulir manual di UI Filament jika AI Gateway sedang tidak merespon. |
| RISK-04 | Perbedaan Perilaku Sintaks Antara SQLite In-Memory (Uji) dan MySQL 8.0 (Produksi) | Dampak: Sedang<br>Probabilitas: Rendah | Menghindari penggunaan fungsi bawaan SQL yang spesifik mesin (seperti `REGEXP` non-standar atau JSON operator khusus) di dalam logika query Eloquent. Semua query kompleks dibungkus dalam repository/scope yang kompatibel lintas dialek SQL. |

---

## 11.2 Buku Besar Utang Teknis (Technical Debt Ledger)

| ID Utang | Komponen Terdampak | Deskripsi Masalah & Batasan Saat Ini | Rencana Remediasi / Target Solusi |
|:---|:---|:---|:---|
| **DEBT-01** | `app/Filament/Pages/MonitorHonorMitra.php` | Penggunaan subquery SQL mentah (`addSelect`) untuk kalkulasi akumulasi honor per mitra bulanan dapat melambat saat jumlah rekaman mitra mencapai puluhan ribu. | Memigrasikan kalkulasi akumulasi honor bulanan ke tabel agregasi terindeks (*materialized summary table*) yang diperbarui secara asinkron via event listener domain saat transaksi honor baru disimpan. |
| **DEBT-02** | `app/Models/Penugasan.php` | Model `Penugasan` memiliki ukuran baris kode mendekati ambang batas batas modularitas (banyak relasi, scope, accessor, dan helper penomoran surat tugas dalam satu berkas). | Melakukan refaktorisasi ke dalam trait/concern modular (misal: `HasApprovalWorkflow`, `HasDocumentConflictDetection`, `HasNumberingSequence`) untuk menjaga SRP (Single Responsibility Principle). |
| **DEBT-03** | `app/Services/WhatsappNotifier.php` | Format pesan WhatsApp masih menggunakan string concatenation di dalam service, belum sepenuhnya memanfaatkan template blade terpisah. | Memindahkan seluruh draf format pesan ke file Blade view khusus (`resources/views/whatsapp/*.blade.php`) agar teks pesan mudah disesuaikan tanpa menyentuh logika kelas service. |
