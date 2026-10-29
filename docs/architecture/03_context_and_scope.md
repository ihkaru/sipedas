# 3. Konteks dan Cakupan Sistem (Context and Scope)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `e02307b` (branch: `main`)
> - **Verified Timestamp**: `2026-10-29T12:40:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memetakan siapa saja yang menggunakan sistem Dokter-V, dengan sistem apa saja sistem terhubung, dan lewat jalur mana data mengalir.

---

## 3.1 Hubungan Pengguna dan Sistem Sekitar

Secara garis besar, Dokter-V menghubungkan pegawai, pimpinan, pengelola keuangan, dan mitra kerja dengan database kantor serta layanan eksternal:

```mermaid
flowchart TD
    subgraph Pengguna["Orang yang Menggunakan Sistem"]
        Pegawai["Pegawai ASN (Pemohon Tugas & Pembuat Laporan)"]
        Pimpinan["Kepala Satker / Plh (Pemberi Izin Penugasan)"]
        Keuangan["PPK & Bendahara (Pemeriksa Berkas Pembayaran)"]
        Mitra["Mitra Statistik (Penerima Honor & BAST)"]
    end

    subgraph DokterV_System["Aplikasi Dokter-V"]
        Core["Modul Persuratan (Surat Tugas & Cek Jadwal Bentrok)"]
        HonorModul["Modul Honor Mitra (Hitung SBM & Cetak SPK/BAST)"]
        KeuanganModul["Verifikasi Keuangan (Pantau Proses Bayar)"]
        LPDAI["LPD AI (Bantu Tulis Laporan Perjalanan)"]
    end

    subgraph LayananLuar["Layanan Luar yang Terhubung"]
        RIDM["rIDM SSO / Local Auth (Login Akun BPS / Akun Lokal)"]
        AinaWA["Gateway WhatsApp (Kirim Pesan & Tautan Approval)"]
        AIGw["Layanan AI Google Gemini (Bantu Tulis Narasi)"]
        GSheets["Google Sheets (Data Calon Mitra Sobat)"]
        DB["Database MySQL Kantor (Simpan Data Transaksi)"]
    end

    Pegawai -->|1. Mengajukan surat tugas & draf laporan| Core
    Pimpinan -->|2. Menyetujui surat tugas via web / WhatsApp| Core
    Keuangan -->|3. Memeriksa tagihan honor dan bayar| KeuanganModul
    Mitra -->|4. Tanda tangan kontrak SPK & serah terima BAST| HonorModul

    Core <-->|Login akun resmi / lokal| RIDM
    Core -->|Kirim pesan WhatsApp| AinaWA
    HonorModul <-->|Tarik data mitra lapangan| GSheets
    LPDAI <-->|Minta bantuan draf narasi| AIGw
    DokterV_System <-->|Simpan dan ambil data| DB
```

---

## 3.2 Daftar Jalur Komunikasi Data (Antarmuka Sistem)

Tabel berikut mencantumkan seluruh jalur masuk dan keluar data pada sistem Dokter-V:

| Antarmuka | Nama Jalur | Cara Berkomunikasi | Apa yang Dilakukan Jalur Ini? |
|:---|:---|:---|:---|
| IF-01 | Filament Web UI | Halaman Web HTTPS | Halaman web utama yang dibuka oleh pegawai, bendahara, dan pimpinan untuk mengisi formulir, melihat data tabel, dan mengunduh berkas PDF/Excel. |
| IF-02 | SAML 2.0 Identity Provider | Jalur Masuk Akun Tunggal (SSO) & Akun Lokal | Mengarahkan pegawai untuk masuk menggunakan akun resmi BPS (rIDM SSO) atau kredensial akun lokal (`AUTH_MODE=email_password`) untuk satker mandiri tanpa integrasi SSO. |
| IF-03 | Agent-Native REST API | Jalur API Data Terprogram | Pintu khusus yang aman (menggunakan kunci rahasia API Key) jika ada program otomatis atau bot asisten yang membutuhkan data penugasan/mitra. |
| IF-04 | WhatsApp Gateway | Pengiriman Pesan WhatsApp | Mengirimkan pesan pemberitahuan otomatis ke nomor HP pegawai atau pimpinan melalui mesin Whatsmeow kantor, dengan cadangan otomatis ke Fonnte. |
| IF-05 | Generative AI Gateway | Layanan Kecerdasan Buatan | Mengirimkan catatan kasar dan foto dari lapangan ke AI Google Gemini untuk dibantu disusun menjadi draf laporan dinas resmi yang rapi. |
| IF-06 | Google Sheets Cloud Integration | Sinkronisasi Lembar Kerja Google | Menghubungkan sistem ke Google Spreadsheet agar koordinator sensus/survei bisa menarik data mitra dari pangkalan data Sobat BPS dengan mudah. |
| IF-07 | Relational Database Persistence | Jalur Penyimpanan Database MySQL | Tempat penyimpanan permanen seluruh data penugasan, nomor surat, nominal honor, bukti potong pajak, dan catatan riwayat perubahan. |
| IF-08 | One-Click Approval Token Webhook | Tautan Persetujuan Kilat di WhatsApp | Tautan aman bertanda tangan kriptografis (`URL::temporarySignedRoute` HMAC-SHA256, 14 hari) di pesan WA pengesah tugas (`/surat-tugas/quick-approve`). Dilengkapi penyaring crawler bot agar persetujuan hanya terjadi saat dibuka peramban pengguna. |

---

## 3.3 Gambaran Konteks Sistem Keseluruhan (Diagram C4Context)

```mermaid
C4Context
    title Peta Konteks Sistem Dokter-V

    Person(pegawai, "Pegawai ASN", "Mengajukan surat tugas dan membuat laporan dinas")
    Person(pimpinan, "Kepala Satker / Plh", "Mengesahkan penugasan dan memeriksa hasil kerja")
    Person(keuangan, "Pengelola Keuangan", "Memverifikasi berkas pembayaran dan mencatat transfer bank")
    
    System(dokterv, "Sistem Dokter-V", "Mengelola surat tugas, honor mitra, verifikasi berkas pembayaran keuangan, dan laporan LPD AI")

    System_Ext(ridm, "rIDM SSO BPS", "Layanan masuk akun tunggal resmi aparatur BPS")
    System_Ext(ainawa, "Gateway WhatsApp Aina", "Pengirim pesan WhatsApp otomatis ke ponsel")
    System_Ext(aigateway, "AI Google Gemini", "Membantu merapikan catatan lapangan menjadi laporan resmi")
    System_Ext(gsheets, "Google Sheets Sobat", "Penyimpanan data calon mitra sensus dan survei")
    SystemDb_Ext(mysqldb, "Database MySQL", "Penyimpan seluruh data penugasan dan bukti pembayaran")

    Rel(pegawai, dokterv, "Buka web untuk buat tugas dan laporan", "IF-01")
    Rel(pimpinan, dokterv, "Setujui tugas lewat web atau pesan WA", "IF-01, IF-08")
    Rel(keuangan, dokterv, "Periksa tagihan dan bayar honor", "IF-01")

    Rel(dokterv, ridm, "Periksa akun pegawai saat login", "IF-02")
    Rel(dokterv, ainawa, "Kirim pemberitahuan dan link persetujuan", "IF-04")
    Rel(dokterv, aigateway, "Minta draf laporan perjalanan dinas", "IF-05")
    Rel(dokterv, gsheets, "Tarik data mitra dan volume kegiatan", "IF-06")
    Rel(dokterv, mysqldb, "Baca dan simpan rekaman transaksi", "IF-07")
```
