# System Requirements Specification (SRS)

## Modul Manajemen Kegiatan Manmit

**Versi:** 1.0
**Tanggal:** 30 Juli 2025

---

### 1. Pendahuluan

#### 1.1. Tujuan

Dokumen ini bertujuan untuk mendefinisikan secara rinci kebutuhan fungsional dan non-fungsional untuk modul "Manajemen Kegiatan Manmit". Modul ini berfungsi sebagai sistem pusat untuk membuat, membaca, memperbarui, dan menghapus (CRUD) data kegiatan, serta menyediakan fitur impor data massal dari berbagai sumber.

#### 1.2. Ruang Lingkup

Perangkat lunak ini adalah sebuah modul yang terintegrasi dalam panel administrasi berbasis Laravel dan Filament. Ruang lingkup dokumen ini mencakup:

-   Struktur data dan model database untuk kegiatan.
-   Fungsionalitas CRUD melalui antarmuka pengguna (UI).
-   Proses pembuatan kegiatan dinamis berdasarkan frekuensi (Tahunan, Semesteran, Triwulanan, Bulanan).
-   Fitur impor data massal dari format JSON.
-   Fitur impor data massal dari file Microsoft Excel (.xlsx).
-   Penyediaan template Excel yang dapat diunduh untuk pengguna.

#### 1.3. Definisi, Akronim, dan Singkatan

-   **SKPL/SRS**: Spesifikasi Kebutuhan Perangkat Lunak / System Requirements Specification.
-   **CRUD**: Create, Read, Update, Delete. Operasi dasar pada data.
-   **UI**: User Interface / Antarmuka Pengguna.
-   **Kegiatan Manmit**: Entitas utama yang dikelola oleh sistem, merepresentasikan sebuah kegiatan atau survei.
-   **Filament**: Kerangka kerja administrasi untuk Laravel yang digunakan untuk membangun UI.
-   **Service Class**: Kelas PHP yang bertanggung jawab atas logika bisnis tertentu.
-   **Importer Class**: Kelas PHP yang bertanggung jawab atas logika impor data dari file.

---

### 2. Deskripsi Keseluruhan

#### 2.1. Perspektif Produk

Modul ini adalah bagian dari sistem administrasi yang lebih besar. Ia berinteraksi langsung dengan database aplikasi (MySQL) dan bergantung pada komponen-komponen utama dari framework Laravel 11 dan Filament v3.

#### 2.2. Fungsi Produk

Sistem akan menyediakan fungsi-fungsi utama sebagai berikut:

1.  **Manajemen Kegiatan**: Pengguna dapat melihat, mencari, dan memfilter daftar semua kegiatan.
2.  **Pembuatan Kegiatan Tunggal dan Berulang**: Menyediakan form dinamis untuk membuat satu kegiatan (misal: Tahunan) atau beberapa kegiatan sekaligus berdasarkan periode (misal: 12 kegiatan bulanan) dalam satu kali proses.
3.  **Pengeditan Kegiatan**: Pengguna dapat mengedit detail dari satu kegiatan individual.
4.  **Penghapusan Kegiatan**: Pengguna dapat menghapus satu atau beberapa kegiatan.
5.  **Impor Data JSON**: Menyediakan antarmuka untuk menyalin-tempel (copy-paste) data JSON untuk impor massal.
6.  **Impor Data Excel**: Menyediakan antarmuka untuk mengunggah file Excel untuk impor massal.
7.  **Penyediaan Template**: Menyediakan tautan untuk mengunduh template Excel kosong yang sesuai dengan format impor.

#### 2.3. Karakteristik Pengguna

Pengguna sistem ini adalah administrator atau staf entri data yang memiliki wewenang untuk mengelola jadwal kegiatan. Mereka diasumsikan memiliki pemahaman dasar dalam menggunakan aplikasi web.

#### 2.4. Batasan (Constraints)

-   Sistem harus dibangun menggunakan Laravel 11.
-   Antarmuka administrasi harus menggunakan Filament v3.
-   Operasi impor/ekspor Excel harus menggunakan pustaka `maatwebsite/excel` v3.
-   Database yang digunakan adalah MySQL.
-   Semua opsi pilihan (seperti frekuensi, jenis kegiatan) harus dikelola secara terpusat dalam kelas `App\Supports\Constants.php`.

---

### 3. Kebutuhan Spesifik

#### 3.1. Kebutuhan Fungsional

##### 3.1.1. Model dan Database (`kegiatan_manmits`)

-   **FR-DB-01**: Tabel `kegiatan_manmits` harus memiliki kolom-kolom berikut:
    -   `id` (VARCHAR, Primary Key): ID unik kegiatan. Tidak boleh auto-increment.
    -   `nama` (VARCHAR): Nama lengkap kegiatan.
    -   `tgl_mulai_pelaksanaan` (TIMESTAMP, Nullable): Tanggal mulai pelaksanaan.
    -   `tgl_akhir_pelaksanaan` (TIMESTAMP, Nullable): Tanggal akhir pelaksanaan.
    -   `tgl_mulai_penawaran` (TIMESTAMP, Nullable): Tanggal mulai penawaran/rekrutmen.
    -   `tgl_akhir_penawaran` (TIMESTAMP, Nullable): Tanggal akhir penawaran/rekrutmen.
    -   `jenis_kegiatan` (ENUM('SENSUS', 'SURVEI'), Nullable, Default: 'SURVEI').
    -   `frekuensi_kegiatan` (ENUM('SUBROUND', 'TAHUNAN', 'TRIWULANAN', 'BULANAN', 'SEMESTERAN'), Nullable).
    -   `created_at` dan `updated_at` (TIMESTAMP).
-   **FR-DB-02**: Model `App\Models\KegiatanManmit` harus dikonfigurasi dengan `$incrementing = false` dan `$keyType = 'string'`.
-   **FR-DB-03**: Model tidak boleh memiliki logika pembuatan `id` otomatis di dalam method `boot()`.

##### 3.1.2. Halaman Daftar Kegiatan (List Page)

-   **FR-LIST-01**: Menampilkan daftar kegiatan dalam bentuk tabel dengan kolom: `Nama`, `Frekuensi Kegiatan`, `Mulai Pelaksanaan`, `Akhir Pelaksanaan`.
-   **FR-LIST-02**: Kolom `Frekuensi Kegiatan` dan `Jenis Kegiatan` harus ditampilkan sebagai "badge" berwarna untuk identifikasi visual.
-   **FR-LIST-03**: Terdapat aksi "Ubah" dan "Hapus" untuk setiap baris.
-   **FR-LIST-04**: Terdapat tiga grup Header Action di bagian atas halaman:
    1.  Tombol "Buat kegiatan manmit".
    2.  Tombol "Impor dari JSON".
    3.  Grup tombol "Aksi Excel" yang berisi "Impor dari Excel" dan "Unduh Template Excel".

##### 3.1.3. Halaman Buat Kegiatan (Create Page)

-   **FR-CREATE-01**: Form harus berisi input untuk "Nama Kegiatan Utama" dan "Frekuensi Kegiatan".
-   **FR-CREATE-02**: Berdasarkan pilihan "Frekuensi Kegiatan", UI harus berubah secara dinamis:
    -   **Jika frekuensi 'TAHUNAN', 'ADHOC', 'PERIODIK'**: Tampilkan dua input `DateTimePicker` untuk tanggal mulai dan akhir pelaksanaan.
    -   **Jika frekuensi 'BULANAN', 'TRIWULANAN', 'SEMESTERAN'**: Tampilkan komponen `Repeater` yang tidak dapat diubah oleh pengguna, yang secara otomatis terisi dengan periode yang relevan (misal: 12 baris untuk bulan). Setiap baris di dalam repeater harus memiliki input `DateTimePicker` untuk tanggal mulai dan akhirnya masing-masing.
-   **FR-CREATE-03**: Saat form disubmit, sistem harus membuat record di database sebagai berikut:
    -   **Untuk frekuensi tunggal**: Satu record dibuat/diperbarui. `id` diambil dari teks di dalam kurung pada nama utama. `nama` adalah nama utama itu sendiri.
    -   **Untuk frekuensi berulang**: Beberapa record dibuat/diperbarui, satu untuk setiap baris di repeater.
        -   `id` dibentuk dengan format: `[id_dasar]-[kunci_periode]` (Contoh: `VHTS25-JANUARI`).
        -   `nama` dibentuk dengan format: `[nama_utama] [nama_periode]` (Contoh: `...VHTS) 2025 Januari`).
        -   Tanggal pelaksanaan diambil dari input yang sesuai untuk setiap periode.
-   **FR-CREATE-04**: Seluruh proses pembuatan harus menggunakan `updateOrCreate` untuk mencegah error duplikasi data.

##### 3.1.4. Halaman Ubah Kegiatan (Edit Page)

-   **FR-EDIT-01**: Form edit harus memuat data dari satu record individual.
-   **FR-EDIT-02**: Form edit tidak boleh berisi logika pembuatan kegiatan berulang. Tujuannya adalah untuk mengedit detail dari satu entri yang sudah ada.
-   **FR-EDIT-03**: Field `id` harus ditampilkan tetapi dalam keadaan non-aktif (disabled).

##### 3.1.5. Fungsionalitas Impor JSON

-   **FR-JSON-01**: Aksi "Impor dari JSON" harus menampilkan modal dengan `Textarea` untuk input JSON.
-   **FR-JSON-02**: Logika impor harus berada di `App\Services\KegiatanManmitService.php`.
-   **FR-JSON-03**: Sistem harus mem-parsing string JSON dan memetakan kunci berikut ke kolom database:
    -   `kd_survei` -> `id`
    -   `(kd_survei) nama_survei` -> `nama`
    -   `tgl_mulai` -> `tgl_mulai_pelaksanaan`
    -   `tgl_selesai` -> `tgl_akhir_pelaksanaan`
    -   `tgl_rek_mulai` -> `tgl_mulai_penawaran`
    -   `tgl_rek_selesai` -> `tgl_akhir_penawaran`
-   **FR-JSON-04**: Sistem harus mengonversi format tanggal ISO 8601 (contoh: `...Z`) menjadi format datetime yang valid untuk MySQL.
-   **FR-JSON-05**: Proses penyimpanan harus menggunakan `updateOrCreate` berdasarkan `id`.
-   **FR-JSON-06**: Menampilkan notifikasi sukses atau gagal setelah proses selesai.

##### 3.1.6. Fungsionalitas Impor Excel

-   **FR-EXCEL-01**: Aksi "Impor dari Excel" harus menampilkan modal dengan komponen `FileUpload`.
-   **FR-EXCEL-02**: Logika impor harus berada di `App\Imports\KegiatanManmitFromUploadImport.php`.
-   **FR-EXCEL-03**: Sistem harus dapat memproses file yang diunggah, mendapatkan path absolutnya di server.
-   **FR-EXCEL-04**: Sistem harus menggunakan `WithMapping` untuk mengonversi format tanggal numerik dari Excel menjadi objek `DateTime`.
-   **FR-EXCEL-05**: Sistem harus menggunakan `WithUpserts` dan `WithHeadingRow` untuk melakukan `updateOrCreate` berdasarkan kolom "ID Kegiatan" di file Excel.
-   **FR-EXCEL-06**: Menampilkan notifikasi sukses atau gagal setelah proses selesai.

##### 3.1.7. Fungsionalitas Unduh Template

-   **FR-TEMPLATE-01**: Aksi "Unduh Template Excel" harus memicu download file `template_kegiatan_manmit.xlsx`.
-   **FR-TEMPLATE-02**: Logika ekspor harus berada di `App\Exports\KegiatanManmitExport.php` dan ditangani oleh sebuah Controller.
-   **FR-TEMPLATE-03**: File Excel yang diunduh harus kosong dan hanya berisi satu baris header dengan kolom: `ID Kegiatan`, `Nama Kegiatan`, `Jenis Kegiatan`, `Tanggal Mulai`, `Tanggal Selesai`, `Frekuensi Kegiatan`.
