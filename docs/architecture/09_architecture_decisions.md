# 9. Keputusan Arsitektur / ADR (Architecture Decisions)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini mendokumentasikan Rekaman Keputusan Arsitektur (*Architecture Decision Records* / ADR) yang memiliki dampak signifikan terhadap struktur, keandalan, dan evolusi sistem SiPEDAS.

---

## ADR-01: Penerapan FrankenPHP Worker Mode menggantikan PHP-FPM Tradisional

- **Status**: Diterima (Accepted)
- **Konteks**:
  SiPEDAS melayani operasi intensif seperti rendering form dinamis Filament v5, evaluasi agregasi data honor ribuan baris, dan penanganan webhook persetujuan instan. Model eksekusi PHP-FPM tradisional yang memuat framework Laravel dari nol pada setiap permintaan (*shared-nothing bootstrap overhead*) menghasilkan latensi 80–150ms per permintaan dan konsumsi CPU tinggi saat diakses bersamaan oleh pegawai satker.
- **Keputusan**:
  Mengadopsi **FrankenPHP** dengan mode Octane worker di dalam kontainer Docker produksi. Framework Laravel tetap berada di memori aplikasi antar permintaan HTTP, memangkas waktu pemrosesan dasar menjadi kurang dari 10ms.
- **Konsekuensi Positif**:
  - Peningkatan throughput hingga 4–5x lipat pada endpoint dashboard Filament.
  - Pemanfaatan server ThinkStation P320 yang jauh lebih efisien dengan footprint CPU minimal.
  - Dukungan streaming HTTP bawaan yang mempermudah respons SSE dan pengunduhan berkas besar.
- **Risiko yang Muncul / Risks created**: RISK-01

---

## ADR-02: Penetapan Single Source of Truth Tanggal Kontrak dan Alokasi Honor

- **Status**: Diterima (Accepted)
- **Konteks**:
  Sebelumnya, entitas honor mitra menyimpan tanggal kontrak, tanggal kegiatan, dan tanggal BAST di atribut terpisah tanpa keterkaitan yang jelas. Hal ini menyebabkan ketidaksinkronan data: honor diinput untuk bulan September, namun tanggal BAST tercatat di bulan Oktober, sehingga menimbulkan anomali pada audit belanja jasa mitra BPK.
- **Keputusan**:
  Menetapkan kolom `honors.tanggal_akhir_kegiatan` sebagai **Sumber Kebenaran Tunggal (Single Source of Truth / SSoT)**. Semua logika bisnis (penentuan bulan batas SBM, periode pencetakan SPK bulanan gabungan survei, tanggal serah terima BAST, dan filter kesiapan alokasi monitoring) diturunkan secara deterministik dari tanggal ini.
- **Konsekuensi Positif**:
  - Meniadakan anomali tanggal pada seluruh laporan dan cetakan PDF.
  - Rekonsiliasi audit keuangan menjadi deterministik dan mudah diverifikasi.
  - Filter monitoring alokasi honor mitra mencerminkan kondisi riil di lapangan.
- **Risiko yang Muncul / Risks created**: RISK-02

---

## ADR-03: Pemanfaatan Gemini LLM Structured JSON Mode untuk Sintesis LPD

- **Status**: Diterima (Accepted)
- **Konteks**:
  Aparatur membutuhkan laporan perjalanan dinas yang formal dan rapi, namun pembuatan laporan secara manual sering tertunda lama. Di sisi lain, memanggil model AI generatif dengan prompt teks bebas sering menghasilkan format narasi yang tidak konsisten, halusinasi fakta, atau struktur dokumen yang terpecah saat dikonversi ke PDF.
- **Keputusan**:
  Mengintegrasikan Google Gemini melalui AI Gateway proxy dengan mengaktifkan parameter `response_format: { type: "json_object" }`. Prompt sistem mewajibkan LLM mengisi skema JSON yang kaku (latar belakang, maksud tugas, rincian tempat/waktu, hasil pelaksanaan, dan simpulan) dengan larangan halusinasi fakta angka dan penegakan tata bahasa PUEBI.
- **Konsekuensi Positif**:
  - Parsing respons AI di backend Laravel menjadi 100% andal tanpa kegagalan ekspresi reguler.
  - Waktu penyusunan LPD oleh pegawai terpangkas dari hitungan jam menjadi hitungan menit.
  - Beban komputasi lokal server ThinkStation tetap 0% (*zero local CPU load*).
- **Risiko yang Muncul / Risks created**: RISK-03

---

## ADR-04: Penegakan Hermetic Testing Guard dengan SQLite In-Memory

- **Status**: Diterima (Accepted)
- **Konteks**:
  Pada iterasi pengembangan sebelumnya, pengujian unit secara tidak sengaja terhubung ke database MySQL kontainer yang sedang berjalan, menyebabkan data uji mencemari data operasional atau pengujian gagal saat koneksi database eksternal terputus.
- **Keputusan**:
  Mengisolasi lingkungan pengujian secara mutlak di dalam file `phpunit.xml` dengan mendeklarasikan koneksi `DB_CONNECTION=sqlite`, database `:memory:`, serta menyertakan atribut `force="true"` pada seluruh variabel lingkungan pengujian.
- **Konsekuensi Positif**:
  - Suite pengujian berjalan secara mandiri (*hermetic*), paralel, dan selesai dalam <5 detik.
  - Menghilangkan risiko kontaminasi database MySQL operasional secara permanen.
  - Mendukung verifikasi Zero-Mock pada logika bisnis relasional Eloquent.
- **Risiko yang Muncul / Risks created**: RISK-04
