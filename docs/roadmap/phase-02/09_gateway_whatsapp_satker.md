# 09. Profil Gateway Notifikasi WhatsApp Satker

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-09`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P2 (Menengah)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](README.md)

---

## 1. Sasaran Modul
Memungkinkan setiap satker menghubungkan nomor bot WhatsApp resmi mereka sendiri secara independen menggunakan berbagai opsi gateway (Whatsmeow/Aina Bot, Fonnte, WPPConnect, atau WAHA) tanpa bergantung pada server bot BPS Mempawah.

---

## 2. Masalah yang Diselesaikan
Layanan [`WhatsappNotifier.php`](file:///home/ihza/Projects/sipedas/app/Services/WhatsappNotifier.php) saat ini memiliki default URL ke server gateway internal Mempawah (`https://aina-wa.dvlpid.my.id`). Jika satker lain menerapkan Dokter-V, pesan notifikasi mereka berisiko gagal terkirim atau bercampur jika server gateway pusat mengalami kendala.

---

## 3. Spesifikasi Teknis

### 3.1 Konfigurasi Multi-Provider Satker
Diatur melalui `.env` satker dan `config/services.php`:
```env
WHATSAPP_ENABLED=true
WHATSAPP_PROVIDER=aina # 'aina' | 'whatsmeow' | 'fonnte' | 'wppconnect' | 'waha' | 'log'
WHATSAPP_BOT_NUMBER=6281234567890

# Opsi Provider Aina / Whatsmeow
WHATSMEOW_BASE_URL=https://wa.satker.go.id
WHATSMEOW_API_KEY=rahasia-satker

# Opsi Provider Fonnte
FONNTE_TOKEN=token-fonnte-satker

# Opsi Development / Mode Simulasi
WHATSAPP_TEST_MODE=false
```

### 3.2 Testing Konektivitas via Panel Pengaturan
Menyediakan tombol *"Tes Kirim Pesan"* di menu Pengaturan Filament:
- Menguji apakah token dan endpoint gateway satker valid.
- Mengirim pesan uji coba ke nomor pengguna yang sedang login.
- Menampilkan pesan error spesifik jika nomor tidak terdaftar atau token kedaluwarsa.

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Satker dapat memilih penyedia gateway WhatsApp yang mereka miliki (misal Fonnte akun kantor atau server Whatsmeow mandiri).
2. Jika gateway tidak aktif (`WHATSAPP_ENABLED=false`), sistem tetap berjalan mulus tanpa error (pesan dialihkan ke file log).
3. Normalisasi nomor telepon dan fitur opt-out pengguna tetap berfungsi 100%.
