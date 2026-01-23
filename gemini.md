# Sikendis Project Progress

## 2026-01-23: God File Detector, Export Pengajuan, & Bulk Approve

### God File Detector Script
- Dibuat script PowerShell `scripts/detect-god-files.ps1` untuk mendeteksi "god files" (file >400 baris)
- Exclude folders: `vendor`, `node_modules`, `.git`, `storage`, `.idea`, `.vscode`, `bootstrap\cache`
- Fitur: custom threshold, output berwarna, export CSV, kategorisasi file

**God Files Ditemukan (7 file):**
| File | Baris | Kategori |
|------|------:|----------|
| `resources\views\surat_tugas\bersama\pdf.blade.php` | 2,693 | View |
| `resources\views\surat_tugas\sendiri\pdf.blade.php` | 2,373 | View |
| `app\Filament\Resources\PenugasanResource.php` | 1,053 | Filament Resource |
| `app\Models\Penugasan.php` | 634 | Model |
| `resources\views\kontrak\pdf.blade.php` | 444 | View |
| `app\Services\Sipancong\PengajuanServices.php` | 431 | Service |
| `app\Models\NomorSurat.php` | 404 | Model |

### Export Pengajuan Feature
- Dibuat `app/Exports/PengajuanExport.php` untuk export data pengajuan ke Excel
- Ditambahkan header action "Export Excel" di halaman list pengajuan (`ListPengajuans.php`)
- Export menggunakan query yang sudah di-filter (sesuai tab/filter yang aktif)
- Menggunakan library `maatwebsite/excel` yang sudah tersedia

### Bulk Approve Feature
- Menambahkan Header Action Dropdown "Terima Massal" di `ListPengajuans.php`
- Opsi: Terima Semua PPK, Terima Semua PPSPM, Terima Semua Bendahara
- Fitur:
    - **Efisien**: Menggunakan single query update (tanpa loop, tanpa event model per item)
    - **No Spam**: Tidak mengirim notifikasi WhatsApp
    - **Konfirmasi**: Menampilkan jumlah data yang akan diapprove sebelum eksekusi
    - **Role-based**: Tombol hanya muncul untuk role yang berwenang

**File yang dimodifikasi/dibuat:**
- `app/Filament/Resources/Sipancong/PengajuanResource/Pages/ListPengajuans.php`
- `app/Services/Sipancong/PengajuanServices.php`
- `app/Exports/PengajuanExport.php`
- `scripts/detect-god-files.ps1`
