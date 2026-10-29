# 11. Risiko dan Catatan Perbaikan (Risks and Technical Debt)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `e02307b` (branch: `main`)
> - **Verified Timestamp**: `2026-10-29T12:40:00+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini mencatat potensi kendala yang perlu diwaspadai dalam pengoperasian sistem Dokter-V serta daftar bagian program yang dijadwalkan untuk dirapikan lebih lanjut.

---

## 11.1 Matriks Risiko Operasional dan Solusinya

| ID | Potensi Masalah / Risiko | Seberapa Besar & Dampaknya? | Cara Mencegah & Mengatasinya |
|:---|:---|:---|:---|
| RISK-01 | Penggunaan Memori Server yang Terus Bertambah Jika Aplikasi Dibiarkan Menyala Terus | Dampak: Sedang<br>Kemungkinan: Rendah | Menghindari penyimpanan data sementara di variabel global. Server Coolify diatur agar otomatis me-refresh aplikasi setiap 500 permintaan agar sisa memori yang tidak terpakai selalu dibersihkan secara berkala. |
| RISK-02 | Data Honor Lama Sebelum Ada Aturan Satu Patokan Tanggal Belum Lengkap | Dampak: Tinggi<br>Kemungkinan: Sedang | Menjalankan program pembersih data otomatis yang melengkapi kolom tanggal selesai kegiatan dari arsip kegiatan survei lama, serta menandai data anomali agar dicek ulang oleh petugas kantor. |
| RISK-03 | Server AI Google Gemini Sedang Mengalami Gangguan atau Lambat Merespons | Dampak: Sedang<br>Kemungkinan: Sedang | Memberikan batas tunggu maksimal 15 detik pada modul laporan. Jika AI sedang tidak merespons, sistem tetap menyediakan formulir manual di layar agar pegawai tetap bisa mengetik dan menyimpan laporannya tanpa terhambat. |
| RISK-04 | Perbedaan Karakteristik Antara Database Pengujian di Memori dan Database Asli Kantor | Dampak: Sedang<br>Kemungkinan: Rendah | Menggunakan perintah query database yang standar dan seragam yang dipahami oleh semua jenis database, sehingga apa yang berhasil di pengujian dipastikan berjalan sama di server kantor. |
| RISK-05 | Ketidaksesuaian Format Master Data CSV saat Migrasi Satker Baru | Dampak: Sedang<br>Kemungkinan: Sedang | Menyediakan validator skema `php artisan satker:validate` dan template CSV resmi di `storage/app/templates/` agar data pegawai/mitra divalidasi format kolom dan tipe datanya sebelum dimasukkan ke database. |
| RISK-06 | Prefetch Otomatis Tautan WhatsApp oleh Crawler Media Sosial Membuka Endpoint Persetujuan | Dampak: Tinggi<br>Kemungkinan: Tinggi | Menambahkan filter deteksi `User-Agent` bot crawler (WhatsApp, Facebook, Twitter, Googlebot). Jika request berasal dari crawler, sistem hanya menyajikan HTML preview OpenGraph tanpa memicu mutasi persetujuan status penugasan. |

---

## 11.2 Catatan Bagian Program yang Perlu Dirapikan (Technical Debt)

Daftar ini adalah pekerjaan rumah pengembang untuk menjaga sistem tetap bersih dan mudah dirawat:

| ID Catatan | Bagian Program | Apa yang Perlu Ditingkatkan? | Rencana Perbaikan ke Depan |
|:---|:---|:---|:---|
| **DEBT-01** | Halaman Pemantauan Honor Mitra (`MonitorHonorMitra.php`) | Perhitungan total honor per mitra saat ini dihitung langsung dari seluruh rekaman database setiap kali halaman dibuka. Jika mitra bertambah hingga puluhan ribu, proses hitung bisa memakan waktu beberapa detik. | Membuat tabel ringkasan tersendiri yang otomatis diperbarui setiap kali ada transaksi honor baru, sehingga membuka halaman pemantauan selalu instan. |
| **DEBT-02** | Berkas Pengelola Surat Tugas (`app/Models/Penugasan.php`) | Berkas kode penugasan saat ini berukuran cukup panjang karena memuat logika surat, nomor otomatis, dan deteksi bentrok sekaligus dalam satu berkas. | Memecah fungsi-fungsi tersebut ke dalam modul pembantu kecil terpisah agar kode program lebih rapi dan lebih mudah dipelajari oleh pengembang baru. |
| **DEBT-03** | Format Pesan WhatsApp (`app/Services/WhatsappNotifier.php`) | Kalimat pesan pemberitahuan WhatsApp saat ini masih dirangkai langsung di dalam baris kode program PHP. | Memindahkan kalimat pesan ke berkas cetakan terpisah (`resources/views/whatsapp/`) agar admin kantor bisa mengubah kata-kata pesan WhatsApp tanpa harus mengubah logika kode program. |
