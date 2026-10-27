# 3. Konteks dan Cakupan (Context and Scope)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memetakan batasan sistem SiPEDAS terhadap sistem eksternal, aktor pengguna, serta antarmuka komunikasi data (antarmuka teknis dan bisnis).

---

## 3.1 Konteks Bisnis (Business Context)

SiPEDAS beroperasi di tengah ekosistem manajemen aparatur, perbendaharaan daerah, dan tata kelola mitra sensus/survei Badan Pusat Statistik (BPS).

```mermaid
flowchart TD
    subgraph Pengguna["Aktor Pengguna"]
        Pegawai["Pegawai ASN / Pemohon"]
        Pimpinan["Kepala Satker & PPSPM / Plh"]
        Keuangan["PPK & Bendahara Pengeluaran"]
        Mitra["Mitra Statistik"]
    end

    subgraph SiPEDAS_System["Batas Sistem: SiPEDAS Platform"]
        Core["SiPEDAS (Surat Tugas & SPPD)"]
        DokterV["Dokter V (Honor Mitra & SPK)"]
        Sipancong["Sipancong (Verifikasi Finansial)"]
        LPDAI["LPD AI (Penyusun Laporan)"]
    end

    subgraph Eksternal["Sistem & Layanan Eksternal"]
        RIDM["rIDM SSO (SAML 2.0 IdP)"]
        AinaWA["Aina Whatsmeow Gateway"]
        AIGw["Google Gemini AI Gateway"]
        GSheets["Google Sheets Cloud (Sobat)"]
        DB["MySQL 8.0 Database"]
    end

    Pegawai -->|1. Ajukan Surat Tugas & Draft LPD| Core
    Pimpinan -->|2. Otorisasi Penugasan & SPK| Core
    Keuangan -->|3. Verifikasi Berkas Tagihan| Sipancong
    Mitra -->|4. Penandatanganan BAST & Output| DokterV

    Core <-->|SAML 2.0 Single Sign-On| RIDM
    Core -->|Notifikasi & Token Approval| AinaWA
    DokterV <-->|Sinkronisasi Data Mitra & Sobat| GSheets
    LPDAI <-->|Sintesis Narasi LPD| AIGw
    SiPEDAS_System <-->|Penyimpanan Transaksional| DB
```

---

## 3.2 Konteks Teknis & Antarmuka Eksternal (Technical Context & Interfaces)

Tabel berikut mendefinisikan seluruh antarmuka integrasi eksternal (`IF-xx`) yang dipertukarkan oleh batas sistem SiPEDAS:

| Antarmuka | Nama Antarmuka | Protokol / Format | Deskripsi & Arah Pertukaran Data |
|:---|:---|:---|:---|
| IF-01 | Filament Web UI | HTTPS / HTML & Livewire | Antarmuka pengguna interaktif bagi ASN, verifikator keuangan, dan pimpinan untuk manajemen tugas, verifikasi honor, dan monitoring. |
| IF-02 | SAML 2.0 Identity Provider | HTTPS / SAML 2.0 XML Post-Binding | Autentikasi Single Sign-On terpusat via rIDM BPS (`/saml/*`) untuk verifikasi identitas aparatur sipil negara. |
| IF-03 | Agent-Native REST API | HTTPS / JSON REST (`/v1/*`) | Antarmuka terprogram untuk agen AI dan klien otomasi dengan autentikasi API Key (`X-API-KEY`) dan spesifikasi OpenAPI/Skill.md. |
| IF-04 | WhatsApp Gateway | HTTPS / JSON REST API | Saluran notifikasi pesan instan transaksional ke pegawai/mitra melalui Aina Whatsmeow Gateway dengan failover otomatis ke Fonnte. |
| IF-05 | Generative AI Gateway | HTTPS / JSON OpenAI-Compatible | Integrasi pemrosesan bahasa alami (LLM Google Gemini) via AI gateway proxy (`ai.dvlpid.my.id/v1/chat/completions`) untuk penulisan LPD. |
| IF-06 | Google Sheets Cloud Integration | HTTPS / Google Sheets v4 API | Integrasi dua arah dengan Google Spreadsheet master mitra/honor untuk impor/ekspor data massal dan integrasi data rekrutmen Sobat BPS. |
| IF-07 | Relational Database Persistence | TCP / MySQL Wire Protocol | Penyimpanan data persisten transaksional (ACID) untuk seluruh relasi data penugasan, honor, bukti potong pajak, dan log audit. |
| IF-08 | One-Click Approval Token Webhook | HTTPS / Signed Token GET Endpoint | Endpoint publik aman (`/approve/{token}`) yang memvalidasi token SHA-256 berbatas waktu untuk persetujuan surat tugas langsung dari tautan WhatsApp. |

---

## 3.3 Diagram Konteks C4 (C4Context)

```mermaid
C4Context
    title C4 Context Diagram - SiPEDAS Platform

    Person(pegawai, "Pegawai ASN", "Pemohon surat tugas & pelapor LPD")
    Person(pimpinan, "Pimpinan Satker / Plh", "Pemberi otorisasi surat tugas & pengesahan SPK")
    Person(keuangan, "Pengelola Keuangan", "PPK & Bendahara pemverifikasi berkas pembayaran")
    
    System(sipedas, "SiPEDAS Enterprise", "Sistem penugasan, LPD AI, tata kelola mitra Dokter V, dan pemantauan keuangan Sipancong")

    System_Ext(ridm, "rIDM SSO IdP", "Identity Provider BPS berbasis SAML 2.0")
    System_Ext(ainawa, "Aina Whatsmeow Gateway", "Gateway pengirim pesan WhatsApp transaksional")
    System_Ext(aigateway, "Gemini AI Gateway", "Layanan LLM Generative AI untuk sintesis narasi LPD")
    System_Ext(gsheets, "Google Sheets Cloud", "Spreadsheet penyimpanan dan sinkronisasi data mitra lapangan")
    SystemDb_Ext(mysqldb, "MySQL 8.0 DB", "Database relasional utama penyimpan seluruh transaksi")

    Rel(pegawai, sipedas, "Mengakses portal mandiri, mengajukan tugas, & mengisi LPD", "IF-01")
    Rel(pimpinan, sipedas, "Mengesahkan penugasan via portal atau pesan instan", "IF-01, IF-08")
    Rel(keuangan, sipedas, "Memverifikasi tagihan honor dan mencatat pencairan", "IF-01")

    Rel(sipedas, ridm, "Autentikasi Single Sign-On pengguna", "IF-02")
    Rel(sipedas, ainawa, "Mengirim notifikasi & token persetujuan", "IF-04")
    Rel(sipedas, aigateway, "Memproses catatan lapangan mentah menjadi LPD resmi", "IF-05")
    Rel(sipedas, gsheets, "Sinkronisasi alokasi honor dan database mitra", "IF-06")
    Rel(sipedas, mysqldb, "Membaca dan menulis data transaksional", "IF-07")
```
