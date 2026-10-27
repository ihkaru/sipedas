# 6. Tinjauan Runtime (Runtime View)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memvisualisasikan interaksi dinamis antar komponen sistem dan entitas eksternal selama eksekusi skenario bisnis kritis.

---

## 6.1 Skenario 1: Pengajuan Surat Tugas, Deteksi Bentrok & One-Click WhatsApp Approval

Skenario ini menggambarkan alur dari saat pegawai mengajukan surat tugas perjalanan dinas hingga pengesahan instan oleh pimpinan melalui tautan WhatsApp:

```mermaid
sequenceDiagram
    autonumber
    actor Pegawai as Pegawai ASN
    participant UI as Filament Web UI (IF-01)
    participant Core as SiPEDAS Core
    participant DB as MySQL DB (IF-07)
    participant WAClient as WhatsApp Gateway Client (IF-04)
    actor Pimpinan as Pimpinan Satker
    participant Webhook as Approval Webhook (IF-08)

    Pegawai->>UI: Input form Surat Tugas (Tanggal, Tujuan, Pegawai)
    UI->>Core: Validasi request & cek tumpang-tindih tanggal
    Core->>DB: Query jadwal penugasan aktif pegawai terkait
    alt Terdeteksi Konflik Jadwal
        Core-->>UI: Blokir pengajuan & kembalikan pesan bentrok
        UI-->>Pegawai: Tampilkan peringatan konflik jadwal dinas
    else Jadwal Bersih (Zero-Conflict)
        Core->>DB: Simpan Penugasan (Status: Menunggu Persetujuan)
        Core->>Core: Generate HMAC SHA-256 Token berbatas waktu
        Core->>WAClient: Kirim notifikasi tugas + tautan /approve/{token}
        WAClient-->>Pimpinan: Pesan WA: "Permohonan Surat Tugas baru. Klik tautan untuk menyetujui."
        
        Pimpinan->>Webhook: Klik tautan /approve/{token} dari ponsel
        Webhook->>Core: Validasi integritas token & masa berlaku
        Core->>DB: Update Penugasan (Status: Disetujui, Tanggal Approval, Pejabat)
        Core->>WAClient: Kirim notifikasi pengesahan ke pemohon
        Webhook-->>Pimpinan: Tampilan halaman sukses persetujuan resmi
        WAClient-->>Pegawai: Pesan WA: "Surat Tugas Anda telah disahkan. PDF siap dicetak."
    end
```

---

## 6.2 Skenario 2: Sintesis Laporan Perjalanan Dinas via LPD AI

Skenario penyusunan LPD otomatis dari catatan lapangan kasar menggunakan LLM Google Gemini:

```mermaid
sequenceDiagram
    autonumber
    actor Pegawai as Pegawai ASN
    participant UI as Filament Web UI (IF-01)
    participant LPDAI as LPD AI Engine
    participant AIGw as Gemini AI Gateway (IF-05)
    participant DB as MySQL DB (IF-07)

    Pegawai->>UI: Buka form LPD, input poin-poin kegiatan, unggah foto, GPS
    UI->>LPDAI: Request generate laporan naratif
    LPDAI->>LPDAI: Rakit system prompt PUEBI & schema JSON mode
    LPDAI->>AIGw: POST /v1/chat/completions (model: gemini-3-flash, json_object)
    Note over AIGw: Pemrosesan NLP, restrukturisasi kalimat resmi, penyusunan bab
    AIGw-->>LPDAI: Kembalikan JSON (Latar Belakang, Maksud, Hasil, Rekomendasi)
    LPDAI->>LPDAI: Parse dan validasi kelengkapan atribut laporan
    LPDAI-->>UI: Render draft formulir LPD di layar editor
    Pegawai->>UI: Review & penyesuaian minor narasi
    Pegawai->>UI: Tekan tombol Simpan & Cetak LPD
    UI->>DB: Simpan rekaman LPD terverifikasi
    UI-->>Pegawai: Unduh berkas PDF LPD resmi berstandar BPS
```

---

## 6.3 Skenario 3: Verifikasi Honor Mitra Dokter V, Batas SBM & SPK/BAST

Alur pengalokasian honor mitra statistik dengan penegakan Single Source of Truth dan plafon SBM:

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin Satker / Ketua Tim
    participant UI as Filament Web UI (IF-01)
    participant DokterV as Dokter V Engine
    participant DB as MySQL DB (IF-07)

    Admin->>UI: Input alokasi honor mitra (Mitra ID, Kegiatan, Volume, tanggal_akhir_kegiatan)
    UI->>DokterV: Evaluasi aturan bisnis honorarium
    DokterV->>DokterV: Ekstrak bulan alokasi dari SSoT tanggal_akhir_kegiatan
    DokterV->>DB: SELECT SUM(honor) mitra pada bulan tersebut
    
    alt Akumulasi Honor > Batas SBM (Survei: Rp 3.353.000 / Sensus: Rp 4.694.000)
        DokterV-->>UI: Lempar BusinessRuleException (Plafon SBM Terlampaui)
        UI-->>Admin: Peringatan merah: Alokasi melebihi batas SBM bulan terkait
    else Dalam Batas Toleransi SBM
        DokterV->>DB: INSERT into honors (Simpan alokasi terverifikasi)
        alt Kegiatan Survei (Bulanan)
            DokterV->>DokterV: Lakukan bundling seluruh alokasi survei pada bulan terkait
            DokterV->>DB: Terbitkan SPK Gabungan Bulanan & draf BAST
        else Kegiatan Sensus (Single Activity)
            DokterV->>DB: Terbitkan SPK Mandiri Berbasis Volume
        end
        DokterV-->>UI: Sukses disimpan & tampilkan tombol cetak SPK/BAST
        UI-->>Admin: Unduh berkas kontrak SPK dan lembar BAST digital
    end
```

---

## 6.4 Skenario 4: Alur Verifikasi Keuangan Sipancong Multi-Tahap

Alur verifikasi pembayaran honorarium atau biaya perjalanan dinas oleh aparatur perbendaharaan:

```mermaid
sequenceDiagram
    autonumber
    actor Bendahara as Bendahara Pengeluaran
    participant UI as Filament Web UI (IF-01)
    participant Sipancong as Sipancong Finance
    participant DB as MySQL DB (IF-07)
    actor PPSPM as PPSPM (Penguji SPM)
    actor PPK as PPK (Pembuat Komitmen)

    Bendahara->>UI: Periksa berkas tagihan & kelengkapan bukti pengeluaran
    UI->>Sipancong: Submit verifikasi kelayakan awal
    Sipancong->>DB: Update status: Terverifikasi Bendahara -> Menunggu PPSPM
    
    PPSPM->>UI: Uji keabsahan pembebanan anggaran & MAK
    UI->>Sipancong: Otorisasi penerbitan SPM
    Sipancong->>DB: Update status: Terverifikasi PPSPM -> Menunggu PPK
    
    PPK->>UI: Validasi kesesuaian output fisik terhadap kontrak
    UI->>Sipancong: Setujui Perintah Pembayaran (SPP/SPM disahkan)
    Sipancong->>DB: Update status: Disetujui PPK -> Siap Dicairkan
    
    Bendahara->>UI: Catat nomor referensi transfer bank / SP2D
    UI->>Sipancong: Eksekusi penutupan pembayaran
    Sipancong->>DB: Update status: Selesai Dicairkan (Audit Log Tercatat)
    UI-->>Bendahara: Tampilkan kwitansi pelunasan sah
```
