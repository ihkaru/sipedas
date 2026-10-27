# 12. Glosarium (Glossary)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Seksi ini memuat daftar istilah domain birokrasi, keuangan negara BPS, dan terminologi arsitektur teknis yang digunakan di dalam sistem SiPEDAS.

---

## 12.1 Istilah Domain Bisnis & Birokrasi BPS

| Istilah / Akronim | Kepanjangan & Definisi | Konteks dalam SiPEDAS |
|:---|:---|:---|
| **BAST** | **Berita Acara Serah Terima**: Dokumen legal bukti penyelesaian pekerjaan dan penyerahan output fisik oleh mitra statistik kepada BPS. | Diterbitkan otomatis oleh Dokter V sebagai syarat wajib pencairan honorarium. |
| **Dokter V** | **Verifikasi dan Monitoring Mitra**: Sub-sistem di dalam SiPEDAS untuk pengelolaan kontrak, SPK, BAST, dan evaluasi honor mitra statistik. | Modul pengelola beban kerja dan kesiapan alokasi mitra. |
| **KPA** | **Kuasa Pengguna Anggaran**: Pejabat yang memperoleh kuasa dari Pengguna Anggaran untuk melaksanakan sebagian kewenangan dan tanggung jawab penggunaan anggaran (Kepala Satker). | Penandatangan utama surat tugas dan pemilik otoritas tertinggi. |
| **LPD** | **Laporan Perjalanan Dinas**: Laporan resmi pertanggungjawaban aparatur setelah melaksanakan tugas dinas luar atau survei lapangan. | Disintesis otomatis oleh modul LPD AI dari catatan kasar menjadi format resmi PUEBI. |
| **MAK** | **Mata Anggaran Keluaran**: Kode klasifikasi belanja negara pada DIPA BPS yang membebankan pembiayaan perjalanan atau pembayaran mitra. | Wajib divalidasi ketersediaannya saat pembuatan surat tugas dan pengajuan honor. |
| **Mitra Statistik** | Tenaga kerja lepas non-ASN yang direkrut oleh BPS untuk melakukan pencacahan lapangan (PCL) atau pengawasan lapangan (PML) survei/sensus. | Entitas penerima honor yang dibatasi oleh aturan SBM. |
| **Plh** | **Pelaksana Harian**: Pejabat yang ditunjuk sementara untuk menjalankan tugas rutin saat pejabat definitif berhalangan/dinas luar. | Didukung oleh workflow pendelegasian wewenang otomatis di SiPEDAS. |
| **PPK** | **Pejabat Pembuat Komitmen**: Pejabat yang diberi kewenangan oleh PA/KPA untuk mengambil keputusan dan/atau tindakan yang dapat mengakibatkan pengeluaran atas beban APBN. | Pengesah kontrak SPK dan pihak yang memerintahkan pembayaran honor. |
| **PPSPM** | **Pejabat Penandatangan Surat Perintah Membayar**: Pejabat yang bertugas menguji keabsahan tagihan dan menerbitkan SPM. | Verifikator tahap kedua pada alur pemantauan keuangan Sipancong. |
| **SBM** | **Standar Biaya Masukan**: Batas tertinggi biaya pengeluaran pemerintah yang ditetapkan melalui Peraturan Menteri Keuangan (PMK). | Membatasi honor mitra bulanan (Maks Rp 3.353.000 untuk survei, Rp 4.694.000 untuk sensus). |
| **Sipancong** | **Sistem Pemantauan Tagihan dan Pembayaran Keuangan**: Modul pelacakan berkas verifikasi keuangan multi-tahap (Bendahara $\rightarrow$ PPSPM $\rightarrow$ PPK). | Menegakkan transparansi dan SLA pencairan dana dinas. |
| **SiPEDAS** | **Sistem Informasi Penugasan dan Administrasi Surat**: Platform induk administrasi penugasan perjalanan dinas dan persuratan resmi BPS. | Menyediakan deteksi bentrok jadwal dan penerbitan surat tugas dinas. |
| **SLS** | **Satuan Lingkungan Setempat**: Wilayah kerja terkecil statistik (RT/RW/Dusun) yang menjadi unit pencacahan sensus/survei. | Digunakan pada penugasan wilayah kerja mitra lapangan. |
| **Sobat BPS** | Sistem Seleksi dan Rekrutmen Calon Mitra Statistik BPS. | Sumber data profil dan identitas mitra yang disinkronkan ke Dokter V. |
| **SPK** | **Surat Perjanjian Kerja**: Kontrak kerja hukum antara BPS (PPK) dengan mitra statistik mengenai hak, kewajiban, dan besaran imbalan jasa. | Dibuat otomatis dalam bentuk bundling bulanan (survei) atau kontrak tunggal (sensus). |

---

## 12.2 Istilah Arsitektur & Teknologi

| Istilah / Akronim | Definisi Teknis & Peran Sistem |
|:---|:---|
| **ACID** | **Atomicity, Consistency, Isolation, Durability**: Empat properti transaksi database yang menjamin integritas data penomoran dan alokasi dana. |
| **ADR** | **Architecture Decision Record**: Dokumen yang mencatat keputusan arsitektural penting bersama konteks dan konsekuensinya. |
| **arc42** | Kerangka kerja standar internasional (ISO/IEC/IEEE 42010) untuk struktur dokumentasi arsitektur sistem perangkat lunak yang komprehensif. |
| **FrankenPHP** | Server aplikasi web modern berbasis Caddy dengan integrasi PHP worker mode langsung di memori untuk throughput tinggi. |
| **Hermetic Testing** | Pengujian perangkat lunak yang sepenuhnya mandiri, terisolasi dari lingkungan eksternal (menggunakan SQLite in-memory), dan dapat dijalankan paralel. |
| **HMAC SHA-256** | Algoritma tanda tangan kriptografis berbasis kunci rahasia untuk menjamin keabsahan token persetujuan surat tugas via WhatsApp tanpa manipulasi. |
| **Q42** | Model kualitas standar yang menyelaraskan karakteristik mutu arsitektur dengan skenario pengujian konkret (misal: `#integrity`, `#compliance`). |
| **RBAC** | **Role-Based Access Control**: Pembatasan hak akses sistem berdasarkan peran pengguna yang dikelola melalui Filament Shield. |
| **SAML 2.0** | **Security Assertion Markup Language**: Standar protokol federasi identitas berbasis XML untuk integrasi Single Sign-On dengan rIDM BPS. |
| **SSoT** | **Single Source of Truth**: Pola arsitektur di mana suatu data hanya memiliki satu titik acuan resmi (`honors.tanggal_akhir_kegiatan`). |
