<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Pengaturan;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Supports\Constants;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function cetakPenugasan($id)
    {
        // if(auth()->user()) abort(403);
        $penugasans = Penugasan::with(['satuSurat', 'suratTugas', 'suratPerjadin', 'kegiatan', 'pegawai', 'plh'])->find($id);
        // dd($penugasans);
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        $plhAktifSaatPengajuan = Plh::getPlhAktif($penugasans->tgl_pengajuan_tugas, true);
        $plhAktifSaatPerjalanan = Plh::getPlhAktif($penugasans->tgl_mulai_tugas, true);
        return view('surat_tugas.sendiri.pdf', [
            'penugasans' => $penugasans,
            "ppk" => $ppk,
            'namaSatker' => Pengaturan::key('NAMA_KAKO')->nilai,
            'namaSatkerTanpaLevelAdministrasi' => Pengaturan::namaSatker(false),
            'plhAktifSaatPengajuan' => $plhAktifSaatPengajuan,
            'plhAktifSaatPerjalanan' => $plhAktifSaatPerjalanan,
        ])->toHtml();
    }
    public function cetakPenugasanBersama($id)
    {
        // if(auth()->user()) abort(403);
        $penugasan = Penugasan::find($id);
        $penugasans = $penugasan->suratTugasBersamaDisetujui(['satuSurat', 'suratTugas', 'suratPerjadin', 'kegiatan', 'pegawai', 'plh']);
        // dd($penugasans);
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        $plhAktifSaatPengajuan = Plh::getPlhAktif($penugasans->first()->tgl_pengajuan_tugas, true);
        $plhAktifSaatPerjalanan = Plh::getPlhAktif($penugasans->first()->tgl_mulai_tugas, true);
        // dd($plhAktifSaatPengajuan,$plhAktifSaatPerjalanan);
        return view('surat_tugas.bersama.pdf', [
            'penugasans' => $penugasans,
            "ppk" => $ppk,
            'namaSatker' => Pengaturan::key('NAMA_KAKO')->nilai,
            'namaSatkerTanpaLevelAdministrasi' => Pengaturan::namaSatker(false),
            "plhAktifSaatPengajuan" => $plhAktifSaatPengajuan,
            "plhAktifSaatPerjalanan" => $plhAktifSaatPerjalanan,
        ])->toHtml();
    }
    public function cetakKontrak(Request $request)
    {
        // Ambil parameter dari request
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit');
        $id_honor_request = $request->input('id_honor') ?? null;

        // Validasi parameter
        if (!$tahun || !$bulan || !$idKegiatanManmit) {
            abort(400, 'Parameter tahun, bulan, dan id_kegiatan_manmit diperlukan.');
        }

        $bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

        // --- AWAL LOGIKA BARU YANG DIPERBAIKI ---

        // Langkah 1: Identifikasi ID Mitra yang relevan.
        // Mitra dianggap relevan jika memiliki alokasi honor dari kegiatan ini DAN pada bulan ini.
        // Patokannya adalah `tanggal_akhir_kegiatan` pada model Honor.
        $targetMitraIds = AlokasiHonor::whereHas('honor', function ($query) use ($idKegiatanManmit, $tahun, $bulan) {
            $query->where('kegiatan_manmit_id', $idKegiatanManmit)
                ->whereYear('tanggal_akhir_kegiatan', $tahun)
                ->whereMonth('tanggal_akhir_kegiatan', $bulan);
        })->distinct()->pluck('mitra_id');

        // Jika tidak ada mitra yang cocok, hentikan proses.
        if ($targetMitraIds->isEmpty()) {
            return "Tidak ada data kontrak untuk dicetak pada kegiatan dan periode yang dipilih.";
        }

        // Langkah 2: Ambil SEMUA alokasi honor untuk mitra-mitra tersebut di bulan yang sama.
        // Di sini kita tidak lagi memfilter berdasarkan idKegiatanManmit, karena kita ingin
        // menampilkan *semua* pekerjaan mitra di bulan itu dalam satu kontrak.
        $alokasiHonorQuery = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'kontrak' => function ($q) {
                return $q->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);
            }
        ])
            ->whereIn('mitra_id', $targetMitraIds) // Hanya untuk mitra yang relevan
            ->whereHas('honor', function ($query) use ($tahun, $bulan) {
                // Filter berdasarkan bulan kontrak yang benar (dari tanggal_akhir_kegiatan)
                $query->whereYear('tanggal_akhir_kegiatan', $tahun)
                    ->whereMonth('tanggal_akhir_kegiatan', $bulan);
            })
            ->whereHas('kontrak'); // Pastikan sudah punya nomor kontrak

        if ($id_honor_request) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $alokasiHonor = $alokasiHonorQuery->get();
        // --- AKHIR LOGIKA BARU YANG DIPERBAIKI ---

        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);
        return view('kontrak.pdf', [
            'alokasiHonor' => $alokasiHonor,
            'tahun' => $tanggalPengajuan->year,
            'bulan' => $tanggalPengajuan->month,
            'ppk' => $ppk,
            'id_honor' => $id_honor_request,
            'id_kegiatan_manmit' => $idKegiatanManmit // Tetap dikirim untuk referensi jika dibutuhkan
        ]);
    }
    public function cetakBast()
    {
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        // Ambil ID Kegiatan dari request. Ini adalah kunci utamanya.
        $id_kegiatan_manmit_request = request('id_kegiatan_manmit') ?? null;
        $bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

        // --- AWAL LOGIKA BARU YANG DIPERBAIKI ---
        $alokasiHonorQuery = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'bast' => function ($q) {
                // Pastikan hanya memuat BAST
                return $q->where('jenis', \App\Supports\Constants::JENIS_NOMOR_SURAT_BAST);
            }
        ])
            ->whereHas('bast'); // Hanya yang sudah punya nomor BAST

        // **FILTER UTAMA**: Tambahkan kondisi ini untuk memfilter berdasarkan ID Kegiatan Manmit
        if ($id_kegiatan_manmit_request) {
            $alokasiHonorQuery->whereHas('honor', function ($query) use ($id_kegiatan_manmit_request) {
                $query->where('kegiatan_manmit_id', $id_kegiatan_manmit_request);
            });
        } else {
            // Jika ID kegiatan tidak ada, filter berdasarkan tanggal akhir seperti sebelumnya sebagai fallback
            // Namun, alur dari tombol "Cetak BAST" seharusnya selalu menyertakan ID kegiatan.
            $alokasiHonorQuery->whereHas('honor.kegiatanManmit', function ($q) use ($tahun, $bulan) {
                $q->whereYear('tgl_akhir_pelaksanaan', $tahun)
                    ->whereMonth('tgl_akhir_pelaksanaan', $bulan);
            });
        }

        // Opsi id_honor tetap dipertahankan jika diperlukan
        $id_honor_request = request('id_honor') ?? null;
        if ($id_honor_request) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $alokasiHonor = $alokasiHonorQuery->get();
        // --- AKHIR LOGIKA BARU YANG DIPERBAIKI ---

        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);

        return view('bast.pdf', [
            'alokasiHonor' => $alokasiHonor,
            'tahun' => $tanggalPengajuan->year,
            'bulan' => $tanggalPengajuan->month,
            'id_honor' => $id_honor_request,
            'ppk' => $ppk,
            // ID Kegiatan Manmit tetap dikirim ke view untuk referensi jika dibutuhkan
            'id_kegiatan_manmit' => $id_kegiatan_manmit_request
        ]);
    }
}
