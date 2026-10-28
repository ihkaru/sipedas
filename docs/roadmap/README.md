# Katalog Rencana Pelaksanaan & Standar Roadmap Dokter-V

> [!NOTE] **Panduan Standar Pengembangan Berkelanjutan**
> Direktori ini memuat seluruh dokumen spesifikasi teknis dan rencana pelaksanaan modular (*implementation plan*) untuk evolusi platform Dokter-V. Untuk melihat gambaran visi strategis dan timeline tingkat tinggi, silakan merujuk ke [**Dokumen Induk Roadmap (`docs/ROADMAP.md`)**](../ROADMAP.md).

---

## 1. Standar Konvensi Struktur Direktori & Penamaan (Best Practices)

Untuk mendukung skalabilitas proyek yang dapat mencakup **puluhan fase implementasi** (dari `phase-01` hingga `phase-50` atau lebih) tanpa menimbulkan polusi berkas (*root directory clutter*), repositori Dokter-V menerapkan standar pengorganisasian berbasis paket modular:

```text
docs/
├── ROADMAP.md                          # Master strategic roadmap (executive timeline & ringkasan)
└── roadmap/
    ├── README.md                      # [File Ini] Katalog direktori, standar penamaan & konvensi
    ├── phase-01/                      # Paket Fase 1: Fondasi Inti & Hardening Operasional
    │   └── README.md                  # Dokumen master rencana & rekam pencapaian Fase 1
    ├── phase-02/                      # Paket Fase 2: Generalisasi Framework Multi-Satker
    │   ├── README.md                  # Dokumen master rencana & matriks modul Fase 2
    │   ├── 01_ssot_konfigurasi_satker.md
    │   ├── 02_tokenizer_penomoran_surat.md
    │   ├── ...
    │   └── 11_fleksibilitas_autentikasi_dan_sso.md
    ├── phase-03/                      # Paket Fase 3: Interoperabilitas Ekosistem BPS
    │   └── README.md                  # Dokumen master rencana Fase 3
    ├── phase-04/                      # Paket Fase 4: Enterprise Multi-Tenant & Autonomous SRE
    │   └── README.md                  # Dokumen master rencana Fase 4
    └── phase-XX/                      # Format terstandarisasi untuk fase-fase berikutnya...
```

### Aturan Baku Penamaan:
1. **Nama Folder Fase (`phase-XX`)**:
   - Selalu menggunakan awalan `phase-` diikuti dua digit angka terpadu dengan awalan nol (*zero-padded 2 digits*, misal: `phase-01`, `phase-02`, ..., `phase-10`, `phase-50`).
   - Format ini menjamin pengurutan abjad alami (*natural alphabetical sorting*) tetap konsisten dan runut di terminal (`ls`), pohon berkas IDE, maupun antarmuka penjelajah GitHub.
2. **Dokumen Induk Fase (`README.md`)**:
   - Setiap folder fase **wajib** memiliki berkas `README.md` sebagai halaman utama (*entry point*).
   - Antarmuka Git (GitHub, GitLab) secara otomatis merender `README.md` saat direktori dibuka.
3. **Dokumen Modul Kerja Bernomor (`01_<slug>.md`, `02_<slug>.md`, dst.)**:
   - Jika suatu fase memiliki cakupan teknis yang besar, pecah pekerjaan ke dalam berkas-berkas terpisah bernomor urut dua digit.
   - Berkas modul kerja menghubungkan rujukannya secara lokal ke `[Induk](README.md)`.

---

## 2. Siklus Hidup Status Fase (Phase Lifecycle States)

Setiap rencana pelaksanaan memiliki status siklus hidup yang terdefinisi:

| Status | Makna & Tindakan Operasional |
|:---|:---|
| **`PROPOSED`** | Gagasan atau spesifikasi arsitektur baru yang diajukan dan sedang ditinjau. Belum dieksekusi. |
| **`ACCEPTED`** | Spesifikasi teknis telah disepakati dan siap dijadwalkan ke dalam sprint pengerjaan. |
| **`IN PROGRESS`** | Sedang dalam tahap implementasi aktif dan pengujian oleh tim pengembang. |
| **`COMPLETED`** | Seluruh kriteria keberhasilan telah terpenuhi, lolos pengujian *Quality Gate*, dan telah dideploy. |
| **`DEPRECATED`** | Rencana tidak lagi berlaku atau telah digantikan oleh spesifikasi fase yang lebih baru. |

---

## 3. Matriks Katalog Fase Implementasi

Berikut adalah daftar paket fase yang telah terdaftar dalam sistem:

| Fase | Nama Paket / Inisiatif Strategis | Dokumen Induk | Jumlah Modul | Status |
|:---:|:---|:---:|:---:|:---:|
| **01** | Fondasi Inti, Kepatuhan Regulasi & Stabilitas Operasional | [**phase-01/README.md**](phase-01/README.md) | 1 Master | `COMPLETED` |
| **02** | Generalisasi Framework Multi-Satker & Decoupling Invarian/Varian | [**phase-02/README.md**](phase-02/README.md) | 11 Modul | `IN PROGRESS` |
| **03** | Interoperabilitas Ekosistem BPS & Sinkronisasi Data Otomatis | [**phase-03/README.md**](phase-03/README.md) | 1 Master | `PLANNED` |
| **04** | Enterprise Cloud Multi-Tenant & Autonomous SRE | [**phase-04/README.md**](phase-04/README.md) | 1 Master | `FUTURE VISION` |

---

## 4. Panduan Menambahkan Fase Baru

Saat merencanakan fase implementasi baru (misal: Fase 5):
1. **Buat Folder Baru**: Buat folder `docs/roadmap/phase-05/`.
2. **Buat Master `README.md`**: Tulis gambaran umum, latar belakang, tujuan, diagram arsitektur, dan matriks deliverable dengan ID rencana `PLN-PHASE-05`.
3. **Pecah Sub-Modul (Opsional)**: Jika fase mencakup banyak fitur independen, buat berkas terpisah seperti `01_submodul_pertama.md`, `02_submodul_kedua.md`.
4. **Daftarkan ke Katalog**: Tambahkan entri baru pada tabel di berkas [README.md](README.md) ini dan perbarui garis waktu pada [ROADMAP.md](../ROADMAP.md).
