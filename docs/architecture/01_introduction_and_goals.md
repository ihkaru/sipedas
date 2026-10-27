# 1. Pengantar dan Tujuan (Introduction and Goals)

> [!INFO] **Architecture Provenance & Freshness**
> - **Verified Commit**: `9cc0f0e` (branch: `main`)
> - **Verified Timestamp**: `2026-10-27T18:56:09+07:00`
> - **Tracked Paths**: `app/`, `routes/`, `config/`, `database/`
> - **Drift Status**: `verified`

Dokumen ini mendefinisikan arsitektur sistem **SiPEDAS** (Sistem Informasi Penugasan dan Administrasi Surat) yang mencakup sub-sistem **Dokter V** (Verifikasi Honor Mitra & SPK), **Sipancong** (Pemantauan Keuangan & Pembayaran), serta modul **LPD AI** (Laporan Perjalanan Dinas Berbasis Generative AI) pada Badan Pusat Statistik (BPS).

---

## 1.1 Gambaran Kebutuhan (Requirements Overview)

SiPEDAS dirancang untuk menyelesaikan friksi birokrasi, fragmentasi data, dan risiko temuan audit pada operasional kantor pemerintah dengan kebutuhan inti:

1. **Otomasi Surat Tugas & SPPD (SiPEDAS Core)**:
   - Manajemen siklus hidup surat tugas dinas dari permohonan, penomoran otomatis, deteksi konflik jadwal antar pegawai, hingga penerbitan cetak PDF resmi.
   - Pendelegasian persetujuan berjenjang (Kepala Satuan Kerja, Kasubbag Tata Usaha, Pejabat Penandatangan Surat Perintah Membayar / PPSPM, dan Pelaksana Harian / Plh).
   - Akselerasi persetujuan instan tanpa login manual melalui token persetujuan aman via WhatsApp (*One-Click Approval*).

2. **Tata Kelola Mitra Statistik & Honorarium (Dokter V / SIKENDIS)**:
   - Standar Sumber Kebenaran Tunggal (*Single Source of Truth*) tanggal kontrak dan honor mitra berbasis `honors.tanggal_akhir_kegiatan`.
   - Penerapan batas Standar Biaya Masukan (SBM 2026): batas maksimum akumulasi honor bulanan Rp 4.694.000 untuk kegiatan Sensus dan Rp 3.353.000 untuk kegiatan Survei.
   - Mekanisme *bundling* kontrak bulanan untuk survei reguler vs kontrak tunggal berbasis volume untuk kegiatan sensus.
   - Pembuatan otomatis Surat Perjanjian Kerja (SPK), Berita Acara Serah Terima (BAST), dan lembar verifikasi honor.
   - Monitoring kesiapan alokasi mitra (Siap Ditugaskan, Berlebih, Kosong, Overload) dengan ekspor laporan multi-sheet Excel untuk perencanaan lapangan.

3. **Verifikasi Keuangan Multi-Tahap (Sipancong)**:
   - Jalur persetujuan berkas tagihan dan pembayaran berjenjang: Bendahara Pengeluaran $\rightarrow$ PPSPM $\rightarrow$ Pejabat Pembuat Komitmen (PPK) $\rightarrow$ Realisasi Pencairan.
   - Penegakan Service Level Agreement (SLA) verifikasi dan riwayat audit (*audit trail*) yang transparan.

4. **Sintesis Laporan Perjalanan Dinas Cerdas (LPD AI)**:
   - Transformasi catatan lapangan mentah, koordinat GPS, dan dokumentasi foto menjadi narasi formal berstandar Pedoman Umum Ejaan Bahasa Indonesia (PUEBI/EYD).
   - Interaksi asinkron melalui gateway AI LLM berkinerja tinggi (*zero local CPU load*).

5. **Notifikasi Tersentralisasi & Kepatuhan Privasi**:
   - Pengiriman pesan WhatsApp transaksional melalui gateway Whatsmeow mandiri dengan saluran cadangan Fonnte.
   - Fitur *opt-out* bertingkat (global, per-kegiatan, per-nomor pegawai) untuk menghormati preferensi komunikasi aparatur.

---

## 1.2 Sasaran Mutu (Quality Goals)

Sasaran mutu arsitektur diprioritaskan berdasarkan model kualitas Q42:

| Prioritas | Tag Mutu | Sasaran Mutu | Motivasi & Dampak Bisnis |
|:---:|:---|:---|:---|
| 1 | #integrity | Integritas Jadwal & Rekonsiliasi Finansial | Mencegah penugasan tumpang-tindih (*zero conflict scheduling*) dan mengeliminasi risiko kelebihan/duplikasi pembayaran honor mitra statistik. |
| 2 | #compliance | Kepatuhan Regulasi SBM & Standar Perbendaharaan | Mematuhi PMK Standar Biaya Masukan (SBM) 2026, aturan perpajakan PPh 21/23 mitra, serta alur otorisasi keuangan negara. |
| 3 | #usability | Efisiensi Kerja & Kemudahan Akses | Mempercepat *turnaround time* persetujuan pimpinan melalui One-Click WhatsApp dan menyediakan antarmuka Filament yang responsif di desktop maupun mobile. |
| 4 | #interoperability | Keterhubungan Ekosistem & Ekstensibilitas Agen | Menghubungkan sistem secara mulus dengan rIDM (SAML 2.0 SSO), Gateway WhatsApp Aina, Google Sheets Cloud, dan REST API ramah LLM/AI-Agent. |
| 5 | #performance | Kecepatan Transaksi & Skalabilitas Dataset | Waktu respon sub-detik untuk rendering ribuan data alokasi honor dan ekspor streaming berkas spreadsheet berukuran besar tanpa *memory exhaustion*. |

---

## 1.3 Pemangku Kepentingan (Stakeholders)

| Peran | Harapan Utama terhadap Sistem | Pengaruh & Keterlibatan |
|:---|:---|:---|
| **Kepala Satker / KPA** | Visibilitas penugasan dinas, otorisasi cepat via mobile/WA, kepatuhan pagu anggaran. | Otoritas penandatangan tertinggi. |
| **Kasubbag TU / PPSPM** | Validasi ketersediaan anggaran, mitigasi konflik personel, pengawasan administrasi persuratan. | Verifikator administratif utama. |
| **PPK (Pejabat Pembuat Komitmen)** | Verifikasi kesesuaian output kerja mitra terhadap kontrak SPK dan keabsahan BAST sebelum pembayaran. | Penanggung jawab komitmen belanja. |
| **Bendahara Pengeluaran** | Pemantauan alur kas, bukti potong pajak, dan ekspor daftar pembayaran bank yang akurat. | Eksekutor pencairan dana. |
| **Ketua Tim Teknis / Koordinator Lapangan** | Kemudahan merekrut mitra, memantau batas sisa alokasi honor, dan membuat SPK otomatis. | Pengguna operasional harian. |
| **Pegawai ASN** | Pengajuan surat tugas mandiri, notifikasi status persetujuan instan, dan pembuatan draft LPD otomatis via AI. | Pemohon perjalanan dinas. |
| **Mitra Statistik** | Transparansi besaran honor, kepastian jadwal survei, dan kemudahan penerimaan BAST. | Penerima manfaat eksternal. |
| **Auditor Eksternal (BPK / Itwil)** | Jejak rekam digital (*audit trail*) utuh, tidak ada manipulasi tanggal berlaku surut, kepatuhan batas SBM. | Pemeriksa kepatuhan hukum. |
