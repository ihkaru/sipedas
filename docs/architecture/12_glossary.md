# 12. Glosarium dan Istilah Penting (Glossary)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `53db284` (branch: `main`)
> - **Verified Timestamp**: `2026-10-28T12:30:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memuat penjelasan istilah sehari-hari yang sering digunakan di lingkungan BPS dan dalam pengelolaan sistem Dokter-V secara sederhana tanpa bahasa teknis yang rumit.

---

## 12.1 Istilah Administrasi dan Kantor BPS

| Istilah | Apa Artinya dalam Bahasa Sehari-hari? | Kegunaannya di Aplikasi |
|:---|:---|:---|
| **Surat Tugas** | Surat perintah dinas resmi yang menyatakan bahwa seorang pegawai ditugaskan bekerja ke suatu tempat pada rentang tanggal tertentu. | Dibuat dan diajukan di modul penugasan, diverifikasi agar jadwalnya tidak bentrok, dan ditandatangani pimpinan. |
| **Mitra Statistik** | Petugas lapangan non-pegawai (masyarakat umum) yang direkrut resmi oleh BPS untuk mendata sensus atau survei lapangan. | Orang yang menerima pembagian tugas sensus/survei dan mendapatkan honorarium. |
| **SPK (Surat Perjanjian Kerja)** | Naskah kontrak kerja resmi antara BPS dengan mitra statistik yang mengatur berapa honor dan tugas yang harus dikerjakan. | Dicetak otomatis dari aplikasi. Untuk survei digabung per bulan, untuk sensus dibuat per kegiatan. |
| **BAST (Berita Acara Serah Terima)** | Surat bukti resmi yang menyatakan bahwa mitra sudah menyelesaikan pekerjaannya dan menyerahkan hasilnya ke kantor BPS. | Dokumen wajib yang harus ada sebelum honor mitra dicairkan oleh bendahara. |
| **SBM (Standar Biaya Masukan)** | Aturan batas maksimal biaya dan honor yang ditetapkan oleh Menteri Keuangan agar uang negara digunakan secara wajar. | Pembatas otomatis: mitra survei maksimal menerima Rp 3.353.000/bulan, mitra sensus maksimal Rp 4.694.000/bulan. |
| **LPD (Laporan Perjalanan Dinas)** | Laporan pertanggungjawaban resmi yang ditulis pegawai setelah pulang dari tugas dinas luar atau survei lapangan. | Disusun dengan bantuan modul AI Google Gemini dari catatan ringkas pegawai agar cepat selesai. |
| **Plh (Pelaksana Harian)** | Pejabat yang ditunjuk untuk menggantikan sementara tugas pimpinan saat pimpinan sedang cuti atau dinas luar kota. | Bisa menyetujui surat tugas di aplikasi secara sah sesuai surat penunjukan. |
| **PPK (Pejabat Pembuat Komitmen)** | Pejabat kantor yang bertanggung jawab atas perjanjian kerja sama dan berhak memerintahkan pembayaran dana kantor. | Menandatangani kontrak SPK dan menyetujui perintah bayar honor di modul Verifikasi Keuangan. |
| **PPSPM (Pejabat Penandatangan SPM)** | Pejabat kantor yang bertugas menguji keabsahan tagihan sebelum diterbitkan surat perintah membayar ke bank. | Memeriksa kuitansi dan mata anggaran belanja pada tahapan kedua verifikasi keuangan. |
| **Bendahara Pengeluaran** | Petugas yang mengurus kas kantor, memotong pajak resmi, dan mentransfer uang pembayaran ke rekening penerima. | Memeriksa berkas awal dan mencatat nomor bukti transfer bank setelah uang terkirim. |
| **MAK (Mata Anggaran Keluaran)** | Kode pos anggaran belanja di dokumen DIPA kantor untuk membiayai suatu kegiatan atau honor. | Wajib dipilih saat mengajukan tugas agar jelas dari pos anggaran mana biaya tersebut dibayar. |
| **Sobat BPS** | Pangkalan data resmi BPS tempat calon mitra mendaftar dan diseleksi. | Data mitra dihubungkan ke aplikasi Dokter V agar riwayat mitra selalu sinkron. |

---

## 12.2 Istilah Teknis yang Disederhanakan

| Istilah Teknis | Penjelasan Sederhana | Mengapa Digunakan? |
|:---|:---|:---|
| **Single Source of Truth (Satu Patokan Tanggal)** | Kebijakan di mana hanya ada satu kolom tanggal (`tanggal_akhir_kegiatan`) yang dijadikan acuan resmi. | Agar tanggal surat kontrak, tanggal kerja, dan tanggal bayar tidak berbeda-beda dan tidak membingungkan auditor. |
| **One-Click Approval** | Mekanisme di mana pimpinan cukup menyentuh tautan persetujuan di pesan WhatsApp dari HP. | Mempercepat izin tugas tanpa pimpinan harus menyalakan komputer dan login ke web aplikasi. |
| **FrankenPHP / Mode Worker** | Mesin server web yang menjaga aplikasi tetap hidup dan bersiap di memori komputer. | Membuat setiap klik menu terbuka dalam sekejap tanpa menunggu aplikasi membaca ulang berkas program. |
| **MySQL Database** | Pangkalan data utama penyimpan seluruh data penugasan, nomor surat, dan kuitansi pembayaran. | Memastikan data kantor tersimpan permanen di harddisk dan tidak hilang saat komputer mati. |
| **SAML 2.0 (Login Akun BPS)** | Standar masuk akun resmi instansi (rIDM). | Pegawai cukup login sekali menggunakan akun email/NIP resmi BPS untuk membuka sistem. |
| **Opt-Out Notifikasi** | Pilihan menu bagi pegawai untuk menolak atau mematikan pesan WhatsApp tertentu. | Menjaga kenyamanan pegawai agar pesan otomatis di ponselnya tidak berlebihan. |
| **Pengujian di Memori (In-Memory Testing)** | Menjalankan tes otomatis di ruang memori sementara yang terpisah. | Menjamin program bebas kesalahan tanpa risiko mengubah atau merusak data asli kantor. |
