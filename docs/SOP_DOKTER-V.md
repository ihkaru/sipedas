# SOP Penggunaan Aplikasi DOKTER-V
*(Digitalisasi dan Otomatisasi Kelola Transaksi Keuangan Elektronik Real-time - Verifikasi)*

Dokumen ini berisi Standar Operasional Prosedur (SOP) secara menyeluruh terkait penggunaan aplikasi administrasi DOKTER-V untuk pengelolaan kegiatan, pembuatan surat tugas, perjanjian kerja, berita acara serah terima, hingga ke proses pemantauan pembayaran yang akan diverifikasi oleh Pejabat berwenang (PPK, PPSPM, Bendahara).

---

## 1. Persiapan Data Master
Tahapan ini dilakukan di awal atau selama ada pembaharuan informasi struktural pada Organisasi. Menu yang digunakan berada pada kategori referensi/master.
1. **Verifikasi Pegawai & Jabatan**: Melalui menu **Pegawai**, pastikan seluruh data pegawai (termasuk NIP/Identitas lain) telah diunggah atau diupdate. Hal ini diperlukan supaya sistem bisa menunjuk pegawai sebagai penanggung jawab atau pemeriksa.
2. **Manajemen Mitra BPS**: Melalui menu **Mitra**, masukan data base mitra yang akan dilibatkan beserta dengan identitas `id_sobat`.
3. **Pengaturan Wilayah Posisi (Master SLS)**: Pastikan daftar Provinsi, Kabupaten/Kota, Kecamatan, dan Kelurahan/Desa sudah ada untuk pilihan rute Perjalanan Dinas.

---

## 2. Pengelolaan Surat Tugas & SPPD
Proses ini dilakukan untuk mengajukan surat tugas dan perjalanan dinas ke atasan dan dicetak secara legal.

### Langkah-langkah Pembuatan Pengajuan Surat Tugas
1. **Navigasi ke Menu**: Masuk ke menu **Surat Tugas** > **Pengajuan**.
2. **Tambah Pengajuan Baru**: Klik tombol Tambah / Buat Pengajuan.
3. **Pilih Jenis dan Peserta**:
   - Pilih jenis surat tugas (Surat Tugas Saja, Perjalanan Dinas Dalam Kota, Perjalanan Dinas Luar Kota, dsb).
   - Tentukan jenis peserta yang akan dinas (Pegawai Saja, Mitra Saja, atau Pegawai + Mitra).
4. **Detail Penugasan**:
   - Masukkan *Kegiatan* yang dikerjakan pegawai tersebut.
   - Tetapkan rentang **Tanggal Mulai** dan **Tanggal Akhir** penugasan. Sistem akan secara cerdas memblokir hari libur/tanggal merah kecuali ini dibolehkan untuk kriteria tertentu.
5. **Detail Perjalanan Dinas** (Jika memilih perjalanan dinas):
   - Masukkan *Level Tujuan Penugasan* (Misalnya: sampai level Provinsi atau sampai level Kecamatan/Desa).
   - Tentukan nama tempat tujuan.
   - Jika butuh waktu tambahan di jalan saat pergi dan pulang, isikan pada "Tambah Hari Awal Perjalanan" dan "Tambah Hari Akhir Perjalanan".
   - Pilih moda transportasi utama yang dipakai.
6. **Simpan**: Setelah disimpan, dokumen akan berstatus Menunggu Persetujuan.

### Persetujuan Surat Tugas (Oleh Pejabat Berwenang)
1. Pejabat/Penyetuju yang berwenang membuka menu pengajuan.
2. Memilih dokumen yang perlu dicheck dengan mengklik aksi lihat detail.
3. Pejabat tersebut bisa menekan aksi:
   - **Setujui**: Untuk meneruskan proses ini dan menggenerate Nomor Surat Tugas secara otomatis menggunakan modul `Nomor Surat`.
   - **Tolak**: Untuk membatalkan rencana penugasan.
   - **Kumpulkan**: Mengamankan form yang membutuhkan diskusi lanjutan / mengembalikan form kepada tim.

---

## 3. Registrasi Kontrak (SPK) & BAST Mitra
Khusus untuk mengelola administrasi pihak eksternal/mitra BPS dari sisi penugasan dan serah terima pekerjaan.

### Pengaturan Alokasi Honor dan Penerbitan SPK
1. **Pilih Kegiatan Mitra**: Buka tabel pada menu **Kegiatan Manmit** (Manajemen Mitra).
2. **Buka Alokasi Honor**: Akan muncul tabel relasi di bagian bawah. Klik button `Alokasikan Honor`.
3. **Pilih Mitra dan Target**: 
   - Pilih jenis honor dan akan muncul kolom untuk mencari mitra dengan *"Status kemitraan aktif pada tahun berjalan"*.
   - Ubah atribut "Target per Satuan" yang disepakati untuk dikerjakan.
4. **Cetak Kontrak (SPK)**: Jika data sudah disimpan, maka akan ada button bertuliskan **Cetak Kontrak [Bulan] [Tahun]** (Misal: Cetak Kontrak Maret 2026). Tombol ini akan otomatis mengunduh file SPK resmi berekstensi PDF untuk ditanda tangani.

### Pencetakan Berita Acara Serah Terima (BAST)
Apabila "Tanggal Akhir Pelaksanaan" suatu Kegiatan Manmit telah ditentukan atau telah lewat, sistem akan membuka kemampuan pencetakan BAST.
1. Melalui menu yang sama (**Alokasi Honor** pada layar Kegiatan Manmit), cari baris milik Mitra yang pekerjaannya telah dinyatakan selesai.
2. Klik opsi **"Cetak BAST"** yang ada pada baris tabel tersebut. File Berita Acara siap dicetak dan dilampirkan.

---

## 4. Proses Monitoring Pembayaran
Tahap akhir di mana sistem mendigitalkan pemenuhan syarat kelengkapan untuk melakukan pembelanjaan/pembayaran (seluruh aliran dokumen seperti Surat Tugas, SPK, dan BAST pada tahap sebelumnya akan berakhir di sini).

### Jenjang Verifikasi Dokumen
Verifikasi Dokumen dilakukan dengan mengunjungi menu di group **Pembayaran** > **Pengajuan (Monitoring Pembayaran)**.
Dashboard tabel tersebut menyediakan filter **Tab** berbasis workflow: `Semua`, `Perlu Perbaikan`, `PPK`, `PPSPM`, `Bendahara`, dan `Selesai`.

Berikut urutannya:
1. **Pengaju (Tim Kegiatan)**: 
   - Memasukkan rekapan pengajuan pencairan (seperti BAST yang rampung atau SPPD). 
   - Mengambil folder link *Google Drive / Cloud* berisi scan berkas lampiran pendukung.
   - Mengubah posisi dokumen menjadi "Di PPK".
2. **Pemeriksaan PPK (Pejabat Pembuat Komitmen)**:
   - PPK dapat membuka tab `PPK`, melihat dokumen yang antre, kemudian membuka "Aksi PPK". 
   - Jika berkas belum lengkap: PPK bisa menandai *Catatan butuh perbaikan* lalu posisi dikembalikan ke "Di Pengaju Pembayaran".
   - Jika berkas lengkap: PPK meloloskan ke tahap SPM, status akan bergeser "Di PPSPM".
3. **Pemeriksaan PPSPM (Pejabat Penandatangan SPM)**:
   - Mirip dengan prosedur di PPK, namun oleh pejabat penerbit SPM.
   - PPSPM menandai bahwa pemeriksaan absah, lalu status dilempar menjadi "Di Bendahara".
4. **Verifikasi dan Aksi Bendahara**:
   - Di sini terdapat kontrol berlapis di Bendahara.
   - **Aksi Verifikasi Bendahara**: Mengecek kewajaran SPP & ketersediaan dana kas.
   - **Proses Pembayaran**: Jika verifikasi oke dan dana siap dicairkan, bendahara mencatat status pengiriman pembayaran. Status pengajuan dikunci menjadi bendera hijau yaitu **Selesai**.

*(Setiap status pembayaran diawasi langsung lewat dashboard depan milik seluruh pemangku kepentingan untuk mengedepankan transparansi real-time).*
