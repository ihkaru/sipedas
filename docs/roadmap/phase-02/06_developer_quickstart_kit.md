# 06. Developer Quickstart Kit & Docker Dev Starter

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-06`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P1 (Penting)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](../02_satker_framework_generalization.md)

---

## 1. Sasaran Modul
Menyediakan panduan orientasi pengembang (*Developer Onboarding Guide*) yang lengkap, teruji, dan bebas friksi, dilengkapi starter kit Docker yang otomatis menyinkronkan dependensi dan konfigurasi satker baru.

---

## 2. Masalah yang Diselesaikan
Banyak pengembang BPS di daerah memiliki lingkungan lokal yang bervariasi (Linux, macOS, Windows WSL2). Tanpa panduan dan kontainerisasi yang rapi, proses instalasi awal sering kali terhambat masalah ekstensi PHP (seperti `pdo_mysql`, `redis`, `gd`, `intl`), versi PHP yang belum sesuai, atau izin hak akses direktori storage.

---

## 3. Spesifikasi Teknis

### 3.1 Berkas Panduan: `docs/DEVELOPER_QUICKSTART.md`
Panduan resmi berisi 3 langkah mudah:

```markdown
# Panduan Cepat Menjalankan Dokter-V di Satker Anda

### Metode 1: Menggunakan Docker Compose (Direkomendasikan)
Prasyarat: Docker Desktop / Docker Engine & Docker Compose.

```bash
# 1. Clone repositori
git clone https://github.com/ihkaru/sipedas.git dokter-v-satker
cd dokter-v-satker

# 2. Salin environment awal
cp .env.example .env

# 3. Jalankan container development
docker compose -f docker-compose.dev.yml up -d

# 4. Jalankan inisialisasi satker interaktif di dalam container
docker exec -it sikendis-app-run php artisan satker:init

# 5. Buka di browser
http://localhost:8200
```

### Metode 2: Menggunakan PHP Lokal
Prasyarat: PHP 8.4+, Composer 2, MySQL/MariaDB, Node.js 22+.

```bash
# 1. Install dependensi
composer install
npm install && npm run build

# 2. Setup database & inisialisasi
cp .env.example .env
php artisan key:generate
# Sesuaikan kredensial DB di .env, lalu:
php artisan migrate
php artisan satker:init

# 3. Jalankan aplikasi
php artisan serve
```
```

### 3.2 Peningkatan `docker-compose.dev.yml`
1. Memastikan nama container dan nama project Docker dapat dikonfigurasi melalui variabel environment:
   ```yaml
   name: ${COMPOSE_PROJECT_NAME:-dokterv}
   ```
2. Memastikan `docker-entrypoint.sh` secara otomatis mendeteksi jika dependensi di volume `vendor` belum ter-install dan menjalankannya secara aman tanpa intervensi manual.

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Pengembang baru yang belum pernah membuka Dokter-V dapat menjalankan aplikasi dalam waktu kurang dari 5 menit menggunakan Docker.
2. Perintah `satker:init` berjalan lancar di dalam container tanpa error permission atau error database.
3. Panduan onboarding diuji dan diverifikasi pada sistem bersih (*clean environment*).
