# Task Checklist

## Tech Stack (Penting untuk riset dokumentasi)

| Komponen | Versi | Catatan |
|---|---|---|
| **Filament** | **v3.3.33** (`^3.2`) | Selalu cari dokumentasi **Filament v3**, bukan v2 atau v4 |
| **Laravel** | **v11.x** | Framework PHP utama |
| **PHP** | **8.4.23** | Runtime di dalam container |
| **Laravel Octane** | **^2.3** | Server: FrankenPHP (bukan Swoole/RoadRunner) |
| **Livewire** | **v3.x** | Komponen reaktif (bundled dengan Filament v3) |
| **Tailwind CSS** | **v3.x** | Filament v3 hanya support Tailwind v3, **bukan v4** |
| **Vite** | **v5.x** | Bundler frontend |

> **⚠️ Penting saat riset internet:** Selalu tambahkan kata kunci `filament v3` atau `filament 3.x` saat mencari dokumentasi. Banyak hasil pencarian menampilkan Filament v2 (API sangat berbeda) atau Filament v4 (masih beta/berubah).

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
