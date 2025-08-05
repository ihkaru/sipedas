<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
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
    public function cetakKontrak()
    {
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        $id_honor_request = request('id_honor') ?? null;
        $bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

        // --- LOGIKA BARU ---
        $alokasiHonorQuery = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'kontrak' => function ($q) {
                // Pastikan hanya memuat kontrak, bukan BAST
                return $q->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);
            }
        ])
            ->whereHas('kontrak') // Hanya yang sudah punya nomor kontrak
            ->whereYear('tanggal_mulai_perjanjian', $tahun) // Gunakan kolom tanggal yang relevan
            ->whereMonth('tanggal_mulai_perjanjian', $bulan);

        if ($id_honor_request) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $alokasiHonor = $alokasiHonorQuery->get();
        // --- AKHIR LOGIKA BARU ---

        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);

        return view('kontrak.pdf', [
            'alokasiHonor' => $alokasiHonor,
            'tahun' => $tanggalPengajuan->year,
            'bulan' => $tanggalPengajuan->month,
            'ppk' => $ppk,
            'id_honor' => $id_honor_request,
        ]);
    }
    public function cetakBast()
    {
        $tahun = request('tahun') ?? now()->year;
        $bulan = request('bulan') ?? now()->month;
        $id_honor_request = request('id_honor') ?? null;
        $bulan = str_pad($bulan, 2, "0", STR_PAD_LEFT);

        // --- LOGIKA BARU ---
        $alokasiHonorQuery = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'bast' => function ($q) {
                // Pastikan hanya memuat BAST
                return $q->where('jenis', Constants::JENIS_NOMOR_SURAT_BAST);
            }
        ])
            ->whereHas('bast') // Hanya yang sudah punya nomor BAST
            // Filter berdasarkan tanggal akhir kegiatan dari relasi honor
            ->whereHas('honor', function ($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal_akhir_kegiatan', $tahun)
                    ->whereMonth('tanggal_akhir_kegiatan', $bulan);
            });

        if ($id_honor_request) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $alokasiHonor = $alokasiHonorQuery->get();
        // --- AKHIR LOGIKA BARU ---

        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::find(Pengaturan::key("NIP_PPK_SATER")->nilai);

        return view('bast.pdf', [
            'alokasiHonor' => $alokasiHonor,
            'tahun' => $tanggalPengajuan->year,
            'bulan' => $tanggalPengajuan->month,
            'id_honor' => $id_honor_request,
            'ppk' => $ppk,
        ]);
    }
}
