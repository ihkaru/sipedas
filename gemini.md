# Task Checklist & Stack Guide

> [!IMPORTANT] **Konvensi Nama & Identitas Aplikasi:**
> - **Dokter V**: Nama resmi dari **keseluruhan aplikasi/platform** ini.
> - **SiPEDAS**: Nama lama aplikasi yang sekarang telah menjadi **codename repositori** Git saja (`ihkaru/sipedas`).
> - **Sipancong**: Hanyalah **codename modul pengajuan & verifikasi berkas pembayaran**.

## Tech Stack (Penting untuk riset dokumentasi)

| Komponen | Versi | Catatan |
|---|---|---|
| **Filament** | **v3.3.33** (`^3.2`) | Selalu cari dokumentasi **Filament v3**, bukan v2 atau v4 |
| **Laravel** | **v11.x / v13.x** | Framework PHP utama |
| **PHP** | **8.4.24** | Runtime di dalam container Docker |
| **Laravel Octane** | **^2.3** | Server: FrankenPHP (bukan Swoole/RoadRunner) |
| **Livewire** | **v3.x** | Komponen reaktif (bundled dengan Filament v3) |
| **Tailwind CSS** | **v3.x** | Filament v3 hanya support Tailwind v3, **bukan v4** |
| **Vite** | **v5.x** | Bundler frontend |

> **⚠️ Penting saat riset internet:** Selalu tambahkan kata kunci `filament v3` atau `filament 3.x` saat mencari dokumentasi. Banyak hasil pencarian menampilkan Filament v2 (API sangat berbeda) atau Filament v4 (masih beta/berubah).

---

## 🐳 Aturan Development Server (WAJIB DOCKER)

1. **SELALU GUNAKAN DOCKER untuk Development Server:**
   - Stack development sudah terkonfigurasi lengkap di `docker-compose.dev.yml` dan script `dev`.
   - **Dilarang** menjalankan `php artisan serve` di host Windows karena PHP host lokal adalah versi 8.3, sedangkan container Docker sudah menggunakan **PHP 8.4** lengkap dengan FrankenPHP + Octane + MySQL + Redis.
   - **Port 8000 di komputer ini adalah COOLIFY DASHBOARD**, BUKAN aplikasi Dokter-V!

2. **Daftar Port & Endpoint Lokal (Docker):**
   - **Aplikasi Web / Login:** [http://localhost:8200/login](http://localhost:8200/login) atau [http://127.0.0.1:8200/login](http://127.0.0.1:8200/login)
   - **Dashboard Filament:** [http://localhost:8200/a](http://localhost:8200/a)
   - **Database (MySQL 8.0):** Port `3309` (`sikendis-db`)
   - **phpMyAdmin:** [http://localhost:8281](http://localhost:8281) (`sikendis-pma`)
   - **Redis Cache & Session:** Port `6379` (`sikendis-redis`)
   - **Vite HMR Server:** Port `5173` (`sikendis-vite`)

3. **Perintah Menjalankan & Mengelola Stack:**
   - **Menjalankan server:**
     ```bash
     docker compose -f docker-compose.dev.yml up -d
     # atau
     bash dev up
     ```
   - **Menjalankan perintah Artisan:**
     ```bash
     docker exec sikendis-app-run php artisan <perintah>
     # atau
     bash dev artisan <perintah>
     ```
   - **Menjalankan Composer:**
     ```bash
     docker exec sikendis-app-run composer <perintah>
     # atau
     bash dev composer <perintah>
     ```
   - **Restart Octane / Reload Worker:**
     ```bash
     docker exec sikendis-app-run php artisan octane:reload
     # atau
     docker restart sikendis-app-run
     ```
   - **Stop server:**
     ```bash
     docker compose -f docker-compose.dev.yml down
     # atau
     bash dev down
     ```

---

## 🚀 Coolify & Production Docker Build Invariants (Pencegahan Build 26 Menit / Exit Code 100)

1. **Sinkronisasi Cache Mount Composer:**
   - Wajib deklarasikan `ENV COMPOSER_CACHE_DIR=/tmp/cache` di Stage 1 (`composer-builder`) agar sejalan dengan `RUN --mount=type=cache,target=/tmp/cache`.
   - Tanpa env var ini, Composer menulis ke direktori default `/root/.cache/composer` sehingga BuildKit mount cache terabaikan dan setiap build selalu cold-download 100% dari internet.
2. **Penangguhan Autoloader di Stage 1 (`--no-autoloader`):**
   - Stage 1 dilarang men-generate classmap autoloader karena `--no-scripts` mem-bypass `pre-autoload-dump` (`Google\Task\Composer::cleanup`), menyisakan 36.282 file `google/apiclient-services` yang membebani I/O disk hingga belasan menit.
   - Pangkas segera 328 service Google API via `find vendor/google/apiclient-services/src ...` di Stage 1.
3. **Ketahanan Fluktuasi Jaringan & Exit Code 100 (`TransportException`):**
   - Exit code 100 di Composer 2.7.5+ adalah `TransportException` (kegagalan unduhan HTTP).
   - Selalu sertakan retry fallback: `(composer install ... || (sleep 5 && composer install ...))`.
4. **Pinning Versi Statis `install-php-extensions`:**
   - Gunakan versi rilis statis (`releases/download/2.12.6/install-php-extensions`), jangan gunakan `/releases/latest/` agar layer ekstensi PHP 100% permanen ter-cache saat Coolify melakukan build `--pull`.

---

- [x] Fix "Attempt to read property 'email' on null" in `Penugasan::canKumpulkan` <!-- id: 0 -->
- [x] Create Custom Landing Page Resource with Premium Ace Editor <!-- id: 100 -->
- [x] Sycned with GitHub repository <!-- id: 101 -->
- [x] Run Docker Development Server (FrankenPHP + MySQL + Redis + phpMyAdmin) <!-- id: 102 -->
- [x] Fix "UrlGenerationException" in KegiatanManmitResource caused by empty ID record <!-- id: 103 -->
- [x] Sync Google Sheet tabs & create premium visualizer dashboard integrated into Filament at /a/jadwal-terpadu <!-- id: 104 -->
    - [x] Fix `Livewire\Exceptions\MultipleRootElementsDetectedException` caused by parsing unescaped HTML characters in script tag.
    - [x] Convert dynamic HTML string template literals in JS script block to use `\x3c` escaping.
    - [x] Replace all FontAwesome dependencies with native inline SVG Heroicons.
    - [x] Apply custom styling to highlight weekend cells with a rose color tint.
    - [x] Map all non-Filament Tailwind colors to standard Filament semantic colors (`success`, `warning`, `danger`, `info`) and resolve theme build purging issues.
- [x] Setup zero-cache dev workflow: OPcache validate_timestamps, Octane --watch --poll <!-- id: 105 -->
- [x] Setup Filament CSS HMR: Vite usePolling + port 5173 expose + refreshPaths + `bash dev css` <!-- id: 106 -->
- [x] Laporan Perjalanan Dinas: Fix header action button (Pengajuan Laporan Perjadin) di tabel dashboard <!-- id: 107 -->
- [x] Laporan Perjalanan Dinas: Selesaikan form pengajuan & integrasi AI Gemini <!-- id: 108 -->
