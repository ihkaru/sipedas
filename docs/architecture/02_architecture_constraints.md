# 2. Batasan Arsitektur (Architecture Constraints)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Arsitektur SiPEDAS diikat oleh sejumlah batasan teknis, regulasi birokrasi, dan infrastruktur operasional yang tidak dapat dinegosiasikan.

---

## 2.1 Batasan Teknis & Runtime (Technical Constraints)

| ID | Batasan | Konteks & Alasan | Dampak terhadap Desain |
|:---|:---|:---|:---|
| **CON-01** | **PHP 8.4 & Laravel 13** | Standar runtime modern enterprise dengan pengetikan ketat (*strict types*), constructor property promotion, match expressions, dan fitur asinkron. | Seluruh kode backend wajib mematuhi standar pengetikan penuh tanpa `mixed` yang tidak beralasan. |
| **CON-02** | **FrankenPHP / Octane Runtime** | Aplikasi dijalankan di atas server aplikasi FrankenPHP dengan Caddy terintegrasi dan worker mode untuk throughput tinggi. | State aplikasi tidak boleh disimpan di variabel statis global (`static $var`) demi mencegah kebocoran state antar request (*memory leak & cross-request pollution*). |
| **CON-03** | **Filament v5 Admin Panel** | Antarmuka pengguna standar berbasis Livewire 3 dan Tailwind CSS yang terstruktur dalam ekosistem Filament. | UI didorong oleh resource, form, dan table schema Filament. Kustomisasi mendalam dilakukan melalui Livewire components dan Blade partials. |
| **CON-04** | **MySQL 8.0 Production Storage** | Relational database produksi yang mendukung transaksi ACID, CTE (*Common Table Expressions*), window functions, dan JSON columns. | Seluruh operasi penulisan kritis (penomoran surat tugas, status pembayaran, pembuatan BAST) wajib dibungkus dalam `DB::transaction`. |
| **CON-05** | **Hermetic In-Memory Testing** | Pengujian unit dan integrasi wajib hermetis menggunakan SQLite in-memory dengan guard `force="true"`. | Menjamin suite pengujian berjalan dalam hitungan detik tanpa membutuhkan koneksi jaringan dan tidak mencemari database produksi. |

---

## 2.2 Batasan Regulasi & Organisasi (Regulatory & Organizational Constraints)

| ID | Batasan | Regulasi Rujukan | Aturan Bisnis yang Diterapkan |
|:---|:---|:---|:---|
| **CON-06** | **Standar Biaya Masukan (SBM) 2026** | Peraturan Menteri Keuangan (PMK) Standar Biaya Masukan Tahun Anggaran 2026. | Batas akumulasi honor mitra bulanan maksimal **Rp 4.694.000** untuk Sensus dan **Rp 3.353.000** untuk Survei. Validasi penolakan otomatis jika alokasi melebihi batas. |
| **CON-07** | **Pemisahan Wewenang Otorisasi Keuangan** | UU Perbendaharaan Negara & Tata Kelola Keuangan BPS. | Pemisahan tegas peran: Pembuat Komitmen (PPK), Penguji Berkas/Penerbit SPM (PPSPM), dan Pembayar (Bendahara Pengeluaran). Pengguna tidak boleh merangkap persetujuan pada berkas yang sama. |
| **CON-08** | **Single Source of Truth Tanggal Kontrak** | Standar Tata Kelola Data Statistik BPS & Audit BPK. | Tanggal akhir kegiatan survei/sensus yang dicatat pada tabel `honors.tanggal_akhir_kegiatan` menjadi acuan tunggal penetapan bulan pengalokasian honor, SPK, dan BAST. |
| **CON-09** | **Penyimpanan Berkas Digital & BAST** | Perka BPS Standar Operasional Prosedur Pengadaan Jasa Mitra. | Berkas SPK dan BAST digital wajib memiliki format penomoran legal resmi dan dapat diunduh dalam bentuk cetakan PDF standar pemerintah. |

---

## 2.3 Batasan Infrastruktur & Operasional (Infrastructure Constraints)

| ID | Batasan | Detail Infrastruktur |
|:---|:---|:---|
| **CON-10** | **Coolify Orchestration** | Deployment dijalankan otomatis melalui Coolify PaaS pada ThinkStation P320 mesh server (`*.dvlpid.my.id`). | Kontainerisasi multi-stage Docker wajib mendukung build terisolasi dengan non-root user dan healthcheck endpoint `/up`. |
| **CON-11** | **Traefik Reverse Proxy & TLS** | Seluruh lalu lintas HTTP publik diarahkan melalui reverse proxy Traefik dengan sertifikat Let's Encrypt otomatis dan penanganan websocket/SSE tanpa buffering. |
| **CON-12** | **Centralized Infisical Secrets** | Kredensial eksternal (API Key AI Gateway, Token WhatsApp, SAML Certs, Google Cloud Service Account) disuntikkan secara dinamis tanpa pernah disimpan dalam git. |
