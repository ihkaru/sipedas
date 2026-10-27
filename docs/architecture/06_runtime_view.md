# 6. Alur Kerja Sistem (Runtime View)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini menceritakan urutan kejadian langkah demi langkah saat pengguna menggunakan fitur-fitur penting dalam sistem SiPEDAS.

---

## 6.1 Alur 1: Mengajukan Surat Tugas, Cek Jadwal Bentrok, dan Setuju via WhatsApp

Alur ini menjelaskan bagaimana pegawai mengajukan tugas dinas hingga disahkan oleh pimpinan lewat pesan WhatsApp:

```mermaid
sequenceDiagram
    autonumber
    actor Pegawai as Pegawai ASN
    participant UI as Halaman Web (IF-01)
    participant Core as SiPEDAS Core
    participant DB as Database MySQL (IF-07)
    participant WAClient as Pengirim WhatsApp (IF-04)
    actor Pimpinan as Kepala Satker / Plh
    participant Webhook as Tautan Persetujuan (IF-08)

    Pegawai->>UI: Buka form Surat Tugas, isi tujuan, tanggal, dan nama anggota tim
    UI->>Core: Periksa apakah ada jadwal yang bertabrakan
    Core->>DB: Cek jadwal dinas seluruh anggota tim pada tanggal tersebut
    alt Jika Ada Pegawai yang Sudah Punya Tugas di Tanggal Sama
        Core-->>UI: Tolak penyimpanan & sebutkan nama pegawai yang bentrok
        UI-->>Pegawai: Muncul peringatan: "Pegawai X sudah ada tugas dinas di tanggal ini"
    else Jika Jadwal Semua Pegawai Kosong
        Core->>DB: Simpan Surat Tugas (Status: Menunggu Persetujuan)
        Core->>Core: Buat tautan persetujuan rahasia berbatas waktu
        Core->>WAClient: Kirim pesan ringkasan tugas + tautan persetujuan ke WhatsApp pimpinan
        WAClient-->>Pimpinan: Pesan WA masuk: "Ada permohonan surat tugas baru. Klik tautan ini untuk menyetujui."
        
        Pimpinan->>Webhook: Buka pesan WA dari HP, lalu sentuh tautan persetujuan
        Webhook->>Core: Cocokkan keaslian tautan dan pastikan belum kedaluwarsa
        Core->>DB: Ubah status menjadi: Disetujui & terbitkan nomor surat resmi
        Core->>WAClient: Kirim kabar ke pemohon bahwa tugas sudah disahkan
        Webhook-->>Pimpinan: Layar HP menampilkan: "Surat Tugas Berhasil Disahkan"
        WAClient-->>Pegawai: Pesan WA masuk: "Surat tugas Anda sudah disahkan pimpinan. Silakan cetak PDF."
    end
```

---

## 6.2 Alur 2: Menyusun Laporan Perjalanan Dinas Dibantu AI (LPD AI)

Alur ketika pegawai pulang dari dinas luar dan ingin membuat draf laporan resmi dengan bantuan AI:

```mermaid
sequenceDiagram
    autonumber
    actor Pegawai as Pegawai ASN
    participant UI as Halaman Web (IF-01)
    participant LPDAI as LPD AI Engine
    participant AIGw as AI Google Gemini (IF-05)
    participant DB as Database MySQL (IF-07)

    Pegawai->>UI: Buka menu LPD, ketik poin ringkas kegiatan, unggah foto dan lokasi
    UI->>LPDAI: Minta bantuan AI untuk merapikan laporan
    LPDAI->>LPDAI: Siapkan instruksi format laporan resmi pemerintah (PUEBI)
    LPDAI->>AIGw: Kirim catatan ke server AI Google Gemini
    Note over AIGw: AI merapikan kalimat, memperbaiki tata bahasa, dan membagi ke bab resmi
    AIGw-->>LPDAI: Kirim balik hasil dalam bentuk isian bab yang rapi
    LPDAI->>LPDAI: Periksa agar semua poin terisi lengkap
    LPDAI-->>UI: Tampilkan draf laporan di layar komputer pegawai
    Pegawai->>UI: Baca dan perbaiki kata-kata bila ada yang kurang pas
    Pegawai->>UI: Klik tombol Simpan dan Unduh PDF
    UI->>DB: Simpan naskah akhir laporan ke database kantor
    UI-->>Pegawai: Berkas PDF laporan resmi siap dicetak dan ditandatangani
```

---

## 6.3 Alur 3: Menginput Honor Mitra, Cek Batas Honor SBM, dan Terbitkan SPK/BAST

Alur bagaimana koordinator tim mengalokasikan kegiatan kepada mitra tanpa melanggar batas aturan pemerintah:

```mermaid
sequenceDiagram
    autonumber
    actor Admin as Admin Satker / Ketua Tim
    participant UI as Halaman Web (IF-01)
    participant DokterV as Dokter V Engine
    participant DB as Database MySQL (IF-07)

    Admin->>UI: Pilih nama mitra, jenis survei, volume kerja, dan tanggal selesai kegiatan
    UI->>DokterV: Periksa aturan batas honor
    DokterV->>DokterV: Ambil bulan honor dari tanggal_akhir_kegiatan
    DokterV->>DB: Hitung total honor yang sudah diterima mitra tersebut di bulan itu
    
    alt Jika Total Honor Melebihi Batas Resmi (Survei > Rp 3.353.000 / Sensus > Rp 4.694.000)
        DokterV-->>UI: Tolak simpan karena melanggar aturan SBM
        UI-->>Admin: Peringatan merah: "Alokasi honor bulan ini melebihi batas SBM"
    else Jika Masih Aman di Bawah Batas
        DokterV->>DB: Simpan alokasi honor ke database
        alt Jika Kegiatan Survei Bulanan
            DokterV->>DokterV: Gabungkan semua survei di bulan yang sama menjadi 1 kontrak kerja
            DokterV->>DB: Terbitkan draf kontrak SPK gabungan dan lembar serah terima (BAST)
        else Jika Kegiatan Sensus
            DokterV->>DB: Terbitkan kontrak SPK per kegiatan sensus
        end
        DokterV-->>UI: Alokasi berhasil disimpan
        UI-->>Admin: Tombol cetak berkas kontrak (SPK) dan BAST langsung aktif
    end
```

---

## 6.4 Alur 4: Tahapan Pemeriksaan Berkas Keuangan (Sipancong)

Alur pemeriksaan berkas pembayaran oleh petugas keuangan kantor sampai dana dicairkan:

```mermaid
sequenceDiagram
    autonumber
    actor Bendahara as Bendahara Pengeluaran
    participant UI as Halaman Web (IF-01)
    participant Sipancong as Sipancong Finance
    participant DB as Database MySQL (IF-07)
    actor PPSPM as Penguji SPM (PPSPM)
    actor PPK as Pejabat Pembuat Komitmen (PPK)

    Bendahara->>UI: Periksa kelengkapan kuitansi dan bukti pengeluaran
    UI->>Sipancong: Berkas dinyatakan lengkap tahap awal
    Sipancong->>DB: Ubah status berkas: Menunggu Uji PPSPM
    
    PPSPM->>UI: Periksa kecukupan pagu anggaran dan nomor mata anggaran (MAK)
    UI->>Sipancong: Sahkan pengujian surat perintah membayar
    Sipancong->>DB: Ubah status berkas: Menunggu Persetujuan PPK
    
    PPK->>UI: Pastikan hasil kerja lapangan sudah selesai dan BAST sudah diteken
    UI->>Sipancong: Setujui perintah bayar
    Sipancong->>DB: Ubah status berkas: Siap Dicairkan ke Rekening
    
    Bendahara->>UI: Lakukan transfer bank dan masukkan nomor referensi transfer/SP2D
    UI->>Sipancong: Tutup proses pembayaran
    Sipancong->>DB: Ubah status berkas: Selesai Dibayarkan
    UI-->>Bendahara: Bukti pelunasan pembayaran tersimpan rapi di riwayat
```
