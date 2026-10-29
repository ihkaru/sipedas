# DOKTER-V
**Digitalisasi dan Otomatisasi Kelola Transaksi Keuangan Elektronik Real-time - Verifikasi**  
*Kerangka Kerja Tata Kelola Penugasan, Perjalanan Dinas, dan Pembayaran Honor Mitra untuk Satuan Kerja BPS Kabupaten/Kota se-Indonesia.*

> [!IMPORTANT] **Pedoman Naming & Identitas Aplikasi (Branding vs. Codename)**
> - **Dokter V**: Nama resmi dari **keseluruhan aplikasi dan platform** ini (telah diterapkan pada UI, header, notifikasi WhatsApp, dan laporan resmi).
> - **SiPEDAS**: Nama historis lama aplikasi yang sekarang **hanya dipertahankan sebagai codename repositori** Git saja (`ihkaru/sipedas`).
> - **Sipancong**: **Bukan nama aplikasi**, melainkan **codename internal untuk modul pengajuan & verifikasi berkas pembayaran** (perjalanan dinas, transport lokal, honor survei/sensus).

---

## 📌 Fitur Utama

- **Pembuatan Surat Tugas & SPPD Otomatis**: Buat surat tugas dan surat perjalanan dinas secara instan dengan nomor resmi dan template baku BPS.
- **Deteksi Bentrok Jadwal Penugasan**: Sistem otomatis mencegah penugasan ganda pada pegawai di tanggal yang sama (*anti-schedule collision*).
- **Kepatuhan Batas Honor SBM 2026**: Validasi otomatis batas kumulatif honor bulanan mitra statistik sesuai Peraturan Menteri Keuangan Standar Biaya Masukan.
- **Konsolidasi Kontrak Bulanan**: Penggabungan kegiatan survei dalam bulan yang sama ke dalam 1 kontrak (SPK & BAST) terpadu.
- **Monitoring & Verifikasi Berkas Pembayaran**: Pantau kelengkapan berkas untuk semua jenis pencairan keuangan (Transport Lokal, Perjalanan Dinas, Honor Survei, Uang Makan/Lembur) dari meja Bendahara Pengeluaran $\rightarrow$ PPSPM $\rightarrow$ PPK $\rightarrow$ Pencairan.
- **Otomasi Persetujuan via WhatsApp**: Notifikasi instan dan aksi persetujuan cepat bagi pimpinan langsung dari HP, lengkap dengan dukungan mode delegasi ke Kasubbag TU (`ppspm`).
- **Penyusunan Laporan Dinas Berbantuan AI (LPD AI)**: Transformasi catatan lapangan dan foto kegiatan menjadi laporan dinas formal sesuai tata bahasa baku pemerintah (PUEBI).

---

## 🗺️ Roadmap & Multi-Satker Framework

Dokter-V sedang berkembang dari aplikasi internal BPS Kabupaten Mempawah menjadi **framework open-source standar bagi seluruh 514 Satker BPS Kabupaten/Kota**.

| Rencana Kerja / Dokumen | Deskripsi | Status |
|:---|:---|:---:|
| 📍 [**Roadmap Pengembangan Dokter-V**](docs/ROADMAP.md) | Visi multi-fase dari aplikasi lokal menuju platform multi-satker nasional. | **Active** |
| 🚀 [**Rencana Pelaksanaan Fase 2**](docs/roadmap/phase-02/README.md) | Rencana aksi terinci: pemisahan invarian/varian, CLI setup wizard, dan modul satker. | **In Progress** |
| 🏛️ [**Standar Arsitektur (GEMINI.md)**](GEMINI.md) | Pedoman rekayasa perangkat lunak, aturan invarian/varian, dan verification gauntlet. | **Active** |

---

## 📚 Indeks Dokumentasi

| Dokumen | Deskripsi |
|:---|:---|
| [Dokumentasi Arsitektur Enterprise (arc42)](docs/architecture/01_introduction_and_goals.md) | Rancangan arsitektur lengkap dalam 12 seksi modular standar arc42 |
| [Aturan Bisnis & Kontrak (Business Rules)](docs/BUSINESS_RULES.md) | Aturan bisnis untuk SPK, BAST, PPK, dan perhitungan tanggal |
| [Standar Operasional Prosedur (SOP)](docs/SOP_DOKTER-V.md) | Alur kerja operasional harian kantor |
| [Spesifikasi LPD AI](docs/SPESIFIKASI_LPD_AI.md) | Spesifikasi teknis otomasi laporan perjalanan dinas berbasis AI |
| [Panduan Produksi (Production Guide)](PRODUCTION_GUIDE.md) | Panduan deployment ke server produksi via Coolify & FrankenPHP |

---

## 💻 Menjalankan di Lingkungan Lokal (Docker Dev)

```bash
# 1. Salin environment
cp .env.example .env

# 2. Jalankan container development (FrankenPHP Octane + MySQL + Redis)
docker compose -f docker-compose.dev.yml up -d

# 3. Akses aplikasi di browser
http://localhost:8200
```

---

&copy; 2024–2026 DOKTER-V — BPS Kabupaten Mempawah & Komunitas Pengembang BPS. All rights reserved.
