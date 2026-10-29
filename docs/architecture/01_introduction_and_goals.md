# 1. Pengantar dan Tujuan (Introduction and Goals)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `2fce49d` (branch: `main`)
> - **Verified Timestamp**: `2026-10-29T17:40:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Dokumen ini menjelaskan rancangan dan cara kerja sistem **Dokter-V** (Digitalisasi dan Otomatisasi Kelola Transaksi Keuangan Elektronik Real-time - Verifikasi). Sistem ini menyatukan pengelolaan surat tugas dinas, pengelolaan honor mitra statistik, pemantauan dan verifikasi berkas pembayaran keuangan, serta pembuatan laporan perjalanan dinas yang dibantu kecerdasan buatan (**LPD AI**) yang dirancang portabel untuk dapat diadopsi oleh seluruh satuan kerja Badan Pusat Statistik (BPS) Kabupaten/Kota di Indonesia.

> [!IMPORTANT] **Klarifikasi Identitas & Taksonomi Penamaan (Branding vs. Codename)**
> Untuk mencegah kerancuan istilah:
> 1. **Dokter V / Dokter-V**: Nama resmi dari **keseluruhan aplikasi dan platform** ini secara utuh.
> 2. **SiPEDAS**: Nama historis lama aplikasi yang saat ini **hanya berfungsi sebagai codename repositori** kode sumber Git (`ihkaru/sipedas`).
> 3. **Sipancong**: Hanyalah **codename internal untuk modul pengajuan dan verifikasi berkas pembayaran** keuangan (perjalanan dinas, transport lokal, honor survei/sensus).

---

## 1.1 Masalah Nyata yang Diselesaikan Sistem

Sistem ini dibuat untuk mengatasi masalah operasional harian di kantor:

1. **Surat Tugas dan Perjalanan Dinas (Modul Persuratan & SPPD)**:
   - **Mencegah jadwal bertabrakan**: Sistem otomatis menolak jika ada pegawai yang ditugaskan ke dua tempat berbeda pada hari yang sama.
   - **Persetujuan cepat 1-klik lewat WhatsApp**: Pimpinan yang sedang rapat atau dinas luar tidak perlu repot membuka laptop dan login ke aplikasi web. Cukup klik tautan bertanda tangan kriptografis di pesan WhatsApp dari HP, surat tugas langsung sah dan nomor resminya terbit seketika.
   - **Mode delegasi persetujuan fleksibel & wewenang Non-SPPD**: Mengatur siapa yang berhak mengesahkan tugas lewat pengaturan `st_approval_mode` (`delegasi_ppspm`, `kepala_only`, `ppspm_only`, `auto_approve`) untuk tugas berbiaya dinas (SPPD), serta kebijakan terpisah `st_non_sppd_mode` (`auto_approve_notify` dengan notifikasi khusus info ke Kasubbag TU, `follow_global`, atau `auto_approve_silent`) untuk tugas dinas lokal tanpa anggaran SPD.
   - **Pendelegasian pejabat**: Jika pimpinan definitif sedang cuti atau dinas, kewenangan penandatanganan otomatis bisa dialihkan ke Pelaksana Harian (Plh).

2. **Pengelolaan Mitra Statistik & Pembayaran Honor (Modul Honor & SBM)**:
   - **Satu patokan tanggal yang pasti**: Tanggal selesai kegiatan survei/sensus (`tanggal_akhir_kegiatan`) menjadi satu-satunya acuan untuk menentukan bulan honor, tanggal kontrak (SPK), dan tanggal serah terima hasil kerja (BAST). Ini mencegah tanggal yang tidak sinkron saat diperiksa auditor.
   - **Pencegahan otomatis batas honor (SBM 2026)**: Sistem otomatis memblokir jika total honor mitra dalam satu bulan melebihi batas aturan pemerintah (maksimal Rp 3.353.000 untuk kegiatan survei dan Rp 4.694.000 untuk kegiatan sensus).
   - **Penggabungan kontrak bulanan**: Kegiatan survei dalam bulan yang sama otomatis digabung ke dalam 1 kontrak bulanan, sedangkan kegiatan sensus dibuatkan kontrak per kegiatan.
   - **Pemantauan beban kerja mitra**: Membantu koordinator lapangan melihat mana mitra yang masih kosong jadwalnya, mana yang pas, dan mana yang sudah penuh agar pembagian tugas merata.

3. **Pemeriksaan Berkas Pembayaran Keuangan (Modul Verifikasi Keuangan)**:
   - **Alur verifikasi yang jelas**: Berkas tagihan diperiksa berurutan dari Bendahara Pengeluaran $\rightarrow$ Penguji SPM (PPSPM) $\rightarrow$ Pembuat Komitmen (PPK) $\rightarrow$ Pencairan ke rekening bank.
   - **Mencegah berkas tertahan**: Riwayat pemeriksaan tercatat jelas sehingga pegawai tahu di meja mana berkas mereka sedang diproses.

4. **Penyusunan Laporan Perjalanan Dinas (LPD AI)**:
   - **Mengurangi beban mengetik**: Pegawai cukup memasukkan poin-poin penting hasil lapangan dan foto kegiatan. AI akan membantu merapikannya menjadi naskah laporan dinas resmi yang rapi sesuai tata bahasa baku pemerintah (PUEBI).

5. **Pengiriman Notifikasi WhatsApp yang Tertib**:
   - Notifikasi dikirim otomatis ke nomor pegawai atau mitra yang bersangkutan.
   - Pegawai yang tidak ingin menerima pesan tertentu bisa mengatur preferensinya sendiri (*opt-out*), dan nomor HP otomatis diseragamkan ke format standar (diawali 628).

---

## 1.2 Sasaran Mutu Utama

Sasaran mutu sistem dirancang agar sistem benar-benar bermanfaat dan aman digunakan:

| Prioritas | Tag Mutu | Sasaran Mutu | Arti Nyata bagi Pengguna |
|:---:|:---|:---|:---|
| 1 | #integrity | Jadwal Tidak Bentrok & Data Honor Tepat | Tidak ada pegawai yang ditugaskan ganda di tanggal yang sama, dan tidak ada mitra yang dibayar dobel. |
| 2 | #compliance | Taat Aturan Batas Honor Pemerintah | Menjamin nominal honor mitra tidak pernah melanggar batas resmi Kementerian Keuangan (SBM 2026). |
| 3 | #usability | Mudah & Cepat Digunakan di HP Maupun Laptop | Pimpinan bisa menyetujui tugas dalam hitungan detik lewat WhatsApp; tampilan web rapi dan nyaman dibuka dari layar HP. |
| 4 | #interoperability | Terhubung Aman dengan Layanan Lain | Masuk cukup pakai akun SSO BPS (rIDM), pesan terkirim lancar lewat WhatsApp, dan laporan dibantu AI secara aman. |
| 5 | #performance | Cepat dan Ringan saat Mengunduh Data | Membuka ribuan data mitra dan mengunduh berkas Excel laporan tetap cepat tanpa membuat komputer kantor lemot atau macet. |

---

## 1.3 Pengguna dan Pemangku Kepentingan

| Peran | Kebutuhan Utama | Manfaat yang Dirasakan |
|:---|:---|:---|
| **Kepala Satker / KPA** | Bisa melihat semua penugasan dinas kantor dan menyetujui tugas mendesak secara cepat dari HP. | Pengesahan tugas tidak tertunda meski sedang berada di luar kantor. |
| **Kasubbag Tata Usaha / Plh** | Memastikan anggaran perjalanan dinas tersedia dan tidak ada pegawai yang jadwalnya bentrok. | Administrasi persuratan tertib dan otomatis bernomor. |
| **PPK (Pejabat Pembuat Komitmen)** | Memastikan hasil kerja mitra sesuai sebelum honor dibayarkan. | Kontrak SPK dan BAST tercetak rapi sesuai aturan audit. |
| **Bendahara Pengeluaran** | Menghitung potongan pajak dengan benar dan mencatat bukti pembayaran bank. | Laporan pencairan dana akurat dan bebas salah hitung. |
| **Ketua Tim / Koordinator Lapangan** | Mengetahui mitra mana yang siap ditugaskan dan berapa sisa batas honor mereka. | Perencanaan survei lapangan berjalan lancar dan adil. |
| **Pegawai ASN** | Mengajukan surat tugas sendiri, menerima pemberitahuan via WA, dan membuat laporan dinas dengan cepat. | Laporan perjalanan dinas selesai tepat waktu tanpa stres mengetik ulang. |
| **Mitra Statistik** | Mendapat kepastian jadwal kerja dan kejelasan besaran honor yang akan diterima. | Menerima kontrak kerja dan bukti serah terima kerja yang jelas. |
| **Pemeriksa / Auditor (BPK / Itwil)** | Mengetahui riwayat pengajuan, tanggal kegiatan, dan dasar pembayaran yang tidak dimanipulasi. | Pemeriksaan keuangan mudah dibuktikan dengan data yang runtut. |
