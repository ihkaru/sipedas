# 02. Mesin Penomoran Surat Berbasis Token (Template Tokenizer)

> [!INFO] **Metadata Modul**
> - **Kode Rencana**: `PLN-P02-02`
> - **Fase**: 2 (Generalisasi Framework Multi-Satker)
> - **Tingkat Prioritas**: P0 (Fondasi Wajib)
> - **Induk**: [02. Rencana Pelaksanaan Fase 2](README.md)

---

## 1. Sasaran Modul
Menghapus seluruh string format nomor surat hardcoded (seperti `/61041/...` dan `/6104/...`) di [`app/Models/NomorSurat.php`](file:///home/ihza/Projects/sipedas/app/Models/NomorSurat.php) dan menggantinya dengan mesin evaluasi template (*token replacement engine*) yang dinamis dan dapat dikustomisasi per jenis naskah dinas.

---

## 2. Masalah yang Diselesaikan
Di dalam [`NomorSurat.php`](file:///home/ihza/Projects/sipedas/app/Models/NomorSurat.php):
- Line 258: `$res = "B-$nomor" . $sub_nomor . "/61041/KP.650/$bulan/$tahun";` (Surat Tugas)
- Line 294: `$res = "$nomor" . $sub_nomor . "/61041/PL.300/$bulan/$tahun";` (SPPD)
- Line 312: `$res = "$nomor" . $sub_nomor . "/6104/BAST/$bulan/$tahun";` (BAST)

Ketika satker lain (misal BPS Kab. Bandung `3204`) menggunakan Dokter-V, nomor surat tugas yang dihasilkan tetap menggunakan kode Mempawah (`61041`) kecuali jika kodenya diubah manual di banyak baris program.

---

## 3. Spesifikasi Teknis

### 3.1 Daftar Token Standar (Placeholders)
Sistem penomoran menyediakan daftar token baku:

| Token | Deskripsi | Contoh Nilai |
|:---|:---|:---|
| `{nomor}` | Nomor urut naskah dinas (3 digit padding) | `042` |
| `{sub_nomor}` | Sub-nomor kegiatan kelompok (opsional) | `.1` atau kosong |
| `{kode_satker}` | Kode 4 digit BPS Satker | `3204`, `6104` |
| `{kode_surat}` | Kode satker persuratan dinas | `32041`, `61041` |
| `{kode_klasifikasi}` | Kode klasifikasi tata kearsipan BPS | `KP.650`, `PL.300`, `VS.100` |
| `{bulan}` | Angka 2 digit bulan penerbitan | `01` s.d. `12` |
| `{bulan_romawi}` | Angka romawi bulan penerbitan | `I`, `II`, `III`, ..., `XII` |
| `{tahun}` | Angka 4 digit tahun penerbitan | `2026` |

### 3.2 Template Standar di `config/satker.php`
```php
'format_surat' => [
    'surat_tugas' => env('FORMAT_SURAT_TUGAS', 'B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}'),
    'sppd'        => env('FORMAT_SURAT_SPPD', '{nomor}{sub_nomor}/{kode_surat}/PL.300/{bulan}/{tahun}'),
    'bast'        => env('FORMAT_SURAT_BAST', '{nomor}{sub_nomor}/{kode_satker}/BAST/{bulan}/{tahun}'),
    'spk'         => env('FORMAT_SURAT_SPK', 'B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}'),
],
```

### 3.3 Mesin Renderer Token di `NomorSurat.php`
```php
public static function renderFormat(string $template, array $data): string
{
    $tokens = [
        '{nomor}'            => $data['nomor'] ?? '',
        '{sub_nomor}'        => $data['sub_nomor'] ?? '',
        '{kode_satker}'      => config('satker.kode', '6104'),
        '{kode_surat}'       => config('satker.kode_surat', config('satker.kode', '6104') . '1'),
        '{kode_klasifikasi}' => $data['kode_klasifikasi'] ?? 'KP.650',
        '{bulan}'            => $data['bulan'] ?? now()->format('m'),
        '{bulan_romawi}'     => self::toRomawi((int) ($data['bulan'] ?? now()->format('m'))),
        '{tahun}'            => $data['tahun'] ?? now()->format('Y'),
    ];

    return str_replace(array_keys($tokens), array_values($tokens), $template);
}
```

---

## 4. Kriteria Keberhasilan (Acceptance Criteria)
1. Seluruh nomor surat tugas, SPPD, SPK, dan BAST yang diterbitkan otomatis mengikuti kode satker yang disetel di `.env` (`SATKER_KODE=3204` $\rightarrow$ nomor terbit `B-001/32041/KP.650/03/2026`).
2. Satker dapat mengubah pola nomor surat secara fleksibel via konfigurasi `.env` tanpa memodifikasi file PHP.
3. Test suite penomoran surat berjalan sukses dengan asserting format token dinamis.
