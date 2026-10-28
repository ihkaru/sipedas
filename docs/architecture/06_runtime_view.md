# 6. Alur Kerja Sistem (Runtime View)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `8765819` (branch: `main`)
> - **Verified Timestamp**: `2026-10-28T12:30:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini menceritakan urutan kejadian langkah demi langkah saat pengguna menggunakan fitur-fitur penting dalam sistem Dokter-V.

---

## 6.1 Alur 1: Mengajukan Surat Tugas, Cek Jadwal Bentrok, dan Setuju 1-Klik via WhatsApp

Alur ini menjelaskan bagaimana pegawai mengajukan tugas dinas hingga disahkan oleh pengesah berwenang (Kepala Satker / Kasubbag TU) lewat pesan WhatsApp:

```mermaid
sequenceDiagram
    autonumber
    actor Pegawai as Pegawai ASN
    participant UI as Halaman Web (IF-01)
    participant Core as Surat Tugas Engine
    participant DB as Database MySQL (IF-07)
    participant WAClient as Pengirim WhatsApp (IF-04)
    actor Approver as Pengesah (Kepala Satker / Kasubbag TU)
    participant Webhook as Tautan Persetujuan Signed Route (IF-08)

    Pegawai->>UI: Buka form Surat Tugas, isi tujuan, tanggal, dan nama anggota tim
    UI->>Core: Periksa apakah ada jadwal yang bertabrakan
    Core->>DB: Cek jadwal dinas seluruh anggota tim pada tanggal tersebut
    alt Jika Ada Pegawai yang Sudah Punya Tugas di Tanggal Sama
        Core-->>UI: Tolak penyimpanan & sebutkan nama pegawai yang bentrok
        UI-->>Pegawai: Muncul peringatan: "Pegawai X sudah ada tugas dinas di tanggal ini"
    else Jika Jadwal Semua Pegawai Kosong
        Core->>DB: Simpan Surat Tugas (Status: Menunggu Persetujuan)
        Core->>Core: Tentukan approver via st_approval_mode & buat temporary signed URL (HMAC SHA-256)
        Core->>WAClient: Kirim pesan ringkasan tugas + tautan persetujuan 1-klik ke WhatsApp approver
        WAClient-->>Approver: Pesan WA masuk: "Ada permohonan surat tugas baru. Klik tautan ini untuk menyetujui."
        
        opt Saat WhatsApp Crawler Mengambil Thumbnail Preview
            Approver->>Webhook: Crawler WhatsApp/Facebook mengirim GET request
            Webhook->>Webhook: Deteksi User-Agent Crawler -> Return preview HTML saja tanpa mengubah status!
        end

        Approver->>Webhook: Approver menyentuh tautan dari peramban ponsel
        Webhook->>Core: Validasi tanda tangan HMAC dan masa kedaluwarsa 14 hari
        Core->>DB: DB Transaction: Ubah status menjadi 'disetujui' & generate nomor surat resmi
        Core->>WAClient: Kirim notifikasi ke pemohon bahwa surat tugas telah disetujui
        Webhook-->>Approver: Halaman respons sukses: "Surat Tugas Berhasil Disetujui (No: B-xxx)"
        WAClient-->>Pegawai: Pesan WA masuk: "Surat tugas Anda telah disetujui. Silakan cetak PDF mandiri."
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

## 6.4 Alur 4: Tahapan Pemeriksaan Berkas Keuangan

Alur pemeriksaan berkas pembayaran oleh petugas keuangan kantor sampai dana dicairkan:

```mermaid
sequenceDiagram
    autonumber
    actor Bendahara as Bendahara Pengeluaran
    participant UI as Halaman Web (IF-01)
    participant Keuangan as Verifikasi Keuangan
    participant DB as Database MySQL (IF-07)
    actor PPSPM as Penguji SPM (PPSPM)
    actor PPK as Pejabat Pembuat Komitmen (PPK)

    Bendahara->>UI: Periksa kelengkapan kuitansi dan bukti pengeluaran
    UI->>Keuangan: Berkas dinyatakan lengkap tahap awal
    Keuangan->>DB: Ubah status berkas: Menunggu Uji PPSPM
    
    PPSPM->>UI: Periksa kecukupan pagu anggaran dan nomor mata anggaran (MAK)
    UI->>Keuangan: Sahkan pengujian surat perintah membayar
    Keuangan->>DB: Ubah status berkas: Menunggu Persetujuan PPK
    
    PPK->>UI: Pastikan hasil kerja lapangan sudah selesai dan BAST sudah diteken
    UI->>Keuangan: Setujui perintah bayar
    Keuangan->>DB: Ubah status berkas: Siap Dicairkan ke Rekening
    
    Bendahara->>UI: Lakukan transfer bank dan masukkan nomor referensi transfer/SP2D
    UI->>Keuangan: Tutup proses pembayaran
    Keuangan->>DB: Ubah status berkas: Selesai Dibayarkan
    UI-->>Bendahara: Bukti pelunasan pembayaran tersimpan rapi di riwayat
```
