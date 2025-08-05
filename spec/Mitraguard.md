# System Requirements Specification (SRS)

**Versi:** 3.0
**Tanggal:** 4 Agustus 2025

---

### 1. Pendahuluan

#### 1.1. Tujuan

Dokumen ini bertujuan untuk mendefinisikan secara rinci kebutuhan fungsional dan non-fungsional untuk modul-modul berikut:

1.  **Manajemen Kegiatan Manmit**: Sistem pusat untuk membuat, membaca, memperbarui, menghapus (CRUD), dan mengimpor data kegiatan.
2.  **Manajemen Mitra & Kemitraan**: Sistem untuk mengelola data master mitra, status keaktifan mereka per tahun, dan menampilkan informasi wilayah berdasarkan data master.
3.  **Manajemen Honor**: Sistem untuk mengelola standar honorarium yang terkait dengan sebuah kegiatan.
4.  **Alokasi Honor**: Sebuah fitur terintegrasi untuk mengalokasikan standar honor kepada mitra secara individual, menggantikan proses manual berbasis spreadsheet.

Modul-modul ini menyediakan fungsionalitas CRUD, fitur impor data massal, dan alur kerja terintegrasi dari pembuatan kegiatan hingga alokasi honor yang siap untuk proses generate dokumen.

#### 1.2. Ruang Lingkup

Perangkat lunak ini adalah serangkaian modul yang terintegrasi dalam panel administrasi berbasis Laravel dan Filament. Ruang lingkup dokumen ini mencakup:

-   Struktur data dan model database untuk Kegiatan, Mitra, Kemitraan, Honor, dan Alokasi Honor.
-   Fungsionalitas CRUD melalui antarmuka pengguna (UI).
-   Alur kerja baru untuk alokasi honor yang interaktif dan efisien.
-   Mekanisme relasi dan aturan bisnis antara Kegiatan, Honor, Mitra, dan Alokasi Honor.
-   Optimalisasi performa pada tampilan data, termasuk _eager loading_ dan penggunaan _indexed accessor_ untuk mencegah masalah N+1 dan pemrosesan data yang lambat.
-   Proses generate dokumen (Kontrak dan BAST) yang memanfaatkan struktur data relasional yang baru.
-   Fitur impor data massal dari format Microsoft Excel (.xlsx) untuk modul-modul master (Kegiatan, Mitra, Honor).
-   Penyediaan template Excel yang dapat diunduh untuk setiap modul impor.
-   Penghentian (Deprecation) proses alokasi honor berbasis impor CSV.

#### 1.3. Definisi, Akronim, dan Singkatan

-   **SKPL/SRS**: Spesifikasi Kebutuhan Perangkat Lunak / System Requirements Specification.
-   **CRUD**: Create, Read, Update, Delete. Operasi dasar pada data.
-   **UI**: User Interface / Antarmuka Pengguna.
-   **Kegiatan Manmit**: Entitas yang merepresentasikan sebuah kegiatan atau survei.
-   **Mitra**: Entitas yang merepresentasikan data master seorang petugas lapangan.
-   **Kemitraan**: Entitas yang merepresentasikan status keaktifan seorang Mitra pada tahun tertentu.
-   **Honor**: Entitas yang merepresentasikan standar biaya untuk sebuah jenis pekerjaan dalam sebuah kegiatan.
-   **Alokasi Honor**: Entitas yang menghubungkan seorang `Mitra` dengan sebuah `Honor` spesifik, serta menyimpan data unik seperti target pekerjaan.
-   **Filament**: Kerangka kerja administrasi untuk Laravel yang digunakan untuk membangun UI.
-   **Relation Manager**: Komponen Filament untuk mengelola data terkait (anak) dari sebuah record utama.
-   **Accessor**: Metode pada model Eloquent yang memungkinkan manipulasi atau pembuatan atribut virtual.

---

### 2. Deskripsi Keseluruhan

#### 2.1. Perspektif Produk

Modul-modul ini adalah bagian dari sistem administrasi yang lebih besar. Mereka berinteraksi langsung dengan database aplikasi (MySQL) dan bergantung pada komponen-komponen utama dari framework Laravel 11 dan Filament v3. Perubahan utama adalah transisi dari proses kerja semi-manual (spreadsheet dan seeder) ke alur kerja yang sepenuhnya terintegrasi di dalam aplikasi.

#### 2.2. Fungsi Produk

Sistem akan menyediakan fungsi-fungsi utama sebagai berikut:

1.  **Manajemen Kegiatan**: Membuat, membaca, mengedit, menghapus, dan mengimpor data kegiatan.
2.  **Manajemen Mitra**: Mengelola data master mitra. Menampilkan data wilayah (kabupaten, kecamatan, desa) yang mudah dibaca berdasarkan referensi ke data master.
3.  **Manajemen Honor**: Mengelola standar honorarium yang dapat diatribusikan ke berbagai kegiatan.
4.  **Manajemen Alokasi Honor**:
    -   Menyediakan antarmuka untuk mengalokasikan satu jenis honor ke banyak mitra sekaligus.
    -   Memungkinkan input target yang berbeda untuk setiap mitra dalam satu proses alokasi.
    -   Menggantikan sepenuhnya proses alokasi honor yang sebelumnya dilakukan di luar aplikasi.
5.  **Impor Massal**: Menyediakan antarmuka untuk mengimpor data master Kegiatan, Mitra, dan Honor dari file Excel.
6.  **Optimasi Performa**: Sistem harus dirancang untuk menangani data dalam jumlah besar (ratusan hingga ribuan baris) dengan cepat, dengan waktu muat halaman tabel di bawah satu detik.
7.  **Generate Dokumen**: Sistem menyediakan fungsionalitas untuk mencetak dokumen Kontrak (per mitra per bulan) dan BAST (per mitra per alokasi honor), dengan mengambil data dari model-model yang saling berelasi.

#### 2.3. Karakteristik Pengguna

Pengguna sistem ini adalah administrator atau staf yang berwenang untuk mengelola kegiatan, data mitra, honor, dan melakukan alokasi. Mereka diasumsikan memiliki pemahaman dasar dalam menggunakan aplikasi web.

#### 2.4. Batasan (Constraints)

-   Sistem harus dibangun menggunakan Laravel 11.
-   Antarmuka administrasi harus menggunakan Filament v3.
-   Operasi impor/ekspor Excel harus menggunakan pustaka `maatwebsite/excel` v3.
-   Database yang digunakan adalah MySQL.

---

### 3. Kebutuhan Spesifik

#### 3.1. Kebutuhan Fungsional

##### 3.1.1. Modul Manajemen Kegiatan Manmit

-   **FR-KEG-DB-01**: Tabel `kegiatan_manmits` tidak berubah.
-   **FR-KEG-REL-01**: Model `KegiatanManmit` harus memiliki relasi `hasMany` ke `Honor`.
-   **FR-KEG-REL-02**: Model `KegiatanManmit` harus memiliki relasi `hasManyThrough` ke `AlokasiHonor` melalui model `Honor`.
-   **FR-KEG-UI-01**: Halaman detail (Edit) dari `KegiatanManmit` harus menampilkan sebuah `RelationManager` baru bernama "Alokasi Honor Mitra".

##### 3.1.2. Modul Manajemen Mitra & Kemitraan

-   **FR-MTR-DB-01**: Tabel `mitras` tidak berubah.
-   **FR-MTR-REL-01**: Model `Mitra` harus memiliki relasi `hasMany` ke `Kemitraan` dan `AlokasiHonor`.
-   **FR-MTR-ACC-01**: Model `Mitra` harus memiliki _accessor_ berikut untuk menampilkan data yang mudah dibaca:
    -   `kabupatenName`: Mengembalikan nama kabupaten dari `master_sls`.
    -   `kecamatanName`: Mengembalikan nama kecamatan dari `master_sls`.
    -   `desaName`: Mengembalikan nama desa/kelurahan dari `master_sls`.
    -   `kemitraansStatus`: Mengembalikan string HTML yang berisi badge berwarna untuk setiap status kemitraan yang dimiliki mitra.
-   **FR-MTR-PERF-01**: Logika di dalam _accessor_ wilayah (`kabupatenName`, `kecamatanName`, `desaName`) harus menggunakan metode pencarian berbasis indeks (array asosiatif) yang di-cache, yang dibuat dari model `MasterSls`, untuk memastikan performa pencarian yang instan (O(1)).
-   **FR-MTR-LIST-01**: Halaman daftar mitra harus menampilkan nama wilayah (Kabupaten, Kecamatan, Desa/Kel) bukan kode wilayah. Kolom kode asli harus disembunyikan secara default tetapi dapat ditampilkan.
-   **FR-MTR-LIST-02**: Halaman daftar mitra harus menampilkan status kemitraan sebagai badge HTML berwarna yang dirender oleh _accessor_ `kemitraansStatus`.
-   **FR-MTR-PERF-02**: Halaman daftar mitra harus menggunakan _eager loading_ (`with(['kemitraans'])`) untuk mencegah masalah N+1 query saat merender status kemitraan.
-   **FR-MTR-FILTER-01**: Halaman daftar mitra harus menyediakan filter `SelectFilter` untuk menyaring mitra berdasarkan Kecamatan dan Desa/Kelurahan domisili mereka.

##### 3.1.3. Modul Manajemen Honor

-   **FR-HNR-DB-01**: Tabel `honors` tidak berubah.
-   **FR-HNR-REL-01**: Model `Honor` harus memiliki relasi `belongsTo` ke `KegiatanManmit` dan `hasMany` ke `AlokasiHonor`.

##### 3.1.4. Modul Alokasi Honor

Modul ini menggantikan proses impor `alokasi_honor.csv`.

-   **FR-ALOK-DB-01**: Tabel `alokasi_honors` harus direstrukturisasi menjadi tabel pivot yang ramping.
    | Nama Kolom | Tipe Data | Keterangan |
    | :--- | :--- | :--- |
    | `id` | `UNSIGNED BIGINT`, PK | ID unik alokasi. |
    | `mitra_id` | `UNSIGNED BIGINT`, FK | Merujuk ke `mitras.id`. |
    | `honor_id` | `VARCHAR`, FK | Merujuk ke `honors.id`. |
    | `target_per_satuan_honor`| `DECIMAL(15, 2)` | Target beban kerja (diinput pengguna). |
    | `total_honor` | `DECIMAL(15, 2)` | Kalkulasi dari (honor.harga x target). |
    | `surat_perjanjian_kerja_id` | `UNSIGNED BIGINT`, FK, Nullable | Merujuk ke `nomor_surats.id`. |
    | `surat_bast_id` | `UNSIGNED BIGINT`, FK, Nullable | Merujuk ke `nomor_surats.id`. |
    | `created_at`, `updated_at` | `TIMESTAMP` | |
-   **FR-ALOK-REL-01**: Model `AlokasiHonor` harus memiliki relasi `belongsTo` ke `Mitra` dan `Honor`.
-   **FR-ALOK-UI-01**: Di dalam `AlokasiHonorRelationManager` pada halaman detail `KegiatanManmit`, harus terdapat sebuah aksi header "Buat Alokasi Baru".
-   **FR-ALOK-UI-02**: Aksi "Buat Alokasi Baru" harus membuka sebuah modal dengan alur kerja sebagai berikut:
    1.  Terdapat `Select` untuk "Pilih Honor untuk Dialokasikan". Komponen ini wajib diisi.
    2.  Terdapat `Select` lain untuk "Cari & Tambah Mitra" yang bersifat _searchable_. Komponen ini dinonaktifkan sampai honor dipilih.
    3.  Ketika pengguna memilih seorang mitra dari `Select` "Cari & Tambah Mitra", mitra tersebut secara dinamis ditambahkan ke dalam sebuah `Repeater` di bawahnya, dan `Select` tersebut di-reset agar pengguna bisa langsung mencari mitra lain.
    4.  `Repeater` menampilkan daftar mitra yang telah dipilih. Setiap baris di dalam `Repeater` berisi nama mitra (non-aktif) dan sebuah input numerik untuk "Target".
    5.  Pengguna dapat menghapus mitra dari daftar di `Repeater` dengan menekan tombol hapus pada baris yang sesuai.
    6.  Setelah mitra pertama ditambahkan ke `Repeater`, `Select` "Pilih Honor" harus dikunci (dinonaktifkan) untuk mencegah perubahan di tengah proses.
-   **FR-ALOK-ACT-01**: Saat form alokasi disubmit, sistem harus melakukan iterasi pada setiap baris data di dalam `Repeater`. Untuk setiap baris, sistem harus membuat satu record baru di tabel `alokasi_honors`, menghubungkan `honor_id` yang dipilih di awal, `mitra_id` dari baris repeater, dan `target_per_satuan_honor` yang diinput.
-   **FR-ALOK-DEP-01**: Proses impor massal untuk alokasi honor menggunakan file `alokasi_honor.csv` dan `AlokasiHonorSeeder` lama harus dihentikan dan digantikan sepenuhnya oleh alur kerja UI yang baru ini. Seeder untuk `alokasi_honors` hanya akan melakukan `truncate` pada tabel.

##### 3.1.5. Modul Generate Dokumen

-   **FR-DOC-PDF-01**: Logika pada `PdfController` untuk `cetakKontrak` dan `cetakBast` harus diperbarui.
