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

class PdfController extends Controller {
    public function cetakPenugasan($id) {
        // if(auth()->user()) abort(403);
        $penugasans = Penugasan::with(['satuSurat', 'suratTugas', 'suratPerjadin', 'kegiatan', 'pegawai', 'plh'])->find($id);
        $ppk = Pegawai::getPpkByDate($penugasans->tgl_pengajuan_tugas);
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
    public function cetakPenugasanBersama($id) {
        // if(auth()->user()) abort(403);
        $penugasan = Penugasan::find($id);
        $penugasans = $penugasan->suratTugasBersamaDisetujui(['satuSurat', 'suratTugas', 'suratPerjadin', 'kegiatan', 'pegawai', 'plh']);
        // dd($penugasans);
        $ppk = Pegawai::getPpkByDate($penugasans->first()->tgl_pengajuan_tugas);
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
    public function cetakKontrak(Request $request) {
        // Ambil parameter dari request
        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $idKegiatanManmit = $request->input('id_kegiatan_manmit');
        $mitraIdRequest = $request->input('mitra_id');
        $id_honor_request = $request->input('id_honor') ?? null;
        $full = $request->has('full');

        // Validasi parameter
        if (!$idKegiatanManmit && !$mitraIdRequest) {
            abort(400, 'Parameter id_kegiatan_manmit atau mitra_id diperlukan.');
        }

        if (!$full && (!$tahun || !$bulan)) {
            abort(400, 'Tahun dan bulan diperlukan jika bukan cetak full.');
        }

        $bulan = $bulan ? str_pad($bulan, 2, "0", STR_PAD_LEFT) : null;

        // Cek jika kegiatan yang diminta adalah SENSUS atau SURVEI
        $kegiatanRequested = $idKegiatanManmit ? \App\Models\KegiatanManmit::find($idKegiatanManmit) : null;
        $isSensusOnly = ($kegiatanRequested?->jenis_kegiatan === 'SENSUS');

        // Langkah 1: Identifikasi ID Mitra yang relevan.
        // Jika idKegiatanManmit diberikan, hanya ambil mitra yang teralokasi pada kegiatan ini.
        $targetMitraIds = AlokasiHonor::whereHas('honor', function ($query) use ($idKegiatanManmit, $tahun, $bulan, $full) {
            if ($idKegiatanManmit) {
                $query->where('kegiatan_manmit_id', $idKegiatanManmit);
            }
            if (!$full) {
                $query->whereYear('tanggal_akhir_kegiatan', $tahun)
                    ->whereMonth('tanggal_akhir_kegiatan', $bulan);
            }
        })
        ->when($mitraIdRequest, fn($q) => $q->where('mitra_id', $mitraIdRequest))
        ->distinct()
        ->pluck('mitra_id');

        // Jika tidak ada mitra yang cocok, hentikan proses.
        if ($targetMitraIds->isEmpty()) {
            return "Tidak ada data kontrak untuk dicetak pada kegiatan dan periode yang dipilih.";
        }

        // Langkah 2: Ambil alokasi honor untuk mitra-mitra tersebut.
        $alokasiHonorQuery = AlokasiHonor::with([
            'mitra',
            'honor.kegiatanManmit',
            'kontrak' => function ($q) {
                return $q->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA);
            }
        ])
            ->whereIn('mitra_id', $targetMitraIds)
            ->whereHas('honor', function ($query) use ($idKegiatanManmit, $isSensusOnly, $tahun, $bulan, $full) {
                if ($idKegiatanManmit && $isSensusOnly) {
                    // SENSUS: khusus kegiatan sensus ini
                    $query->where('kegiatan_manmit_id', $idKegiatanManmit);
                } elseif ($idKegiatanManmit && !$isSensusOnly) {
                    // SURVEI: gabungkan seluruh kegiatan SURVEI mitra di bulan tersebut
                    $query->whereHas('kegiatanManmit', function($q) {
                        $q->where('jenis_kegiatan', '!=', 'SENSUS');
                    });
                }
                if (!$full) {
                    $query->whereYear('tanggal_akhir_kegiatan', $tahun)
                        ->whereMonth('tanggal_akhir_kegiatan', $bulan);
                }
            })
            ->whereHas('kontrak'); // Pastikan sudah punya nomor kontrak

        if ($id_honor_request && !$full) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $allAlokasiHonor = $alokasiHonorQuery->get();

        if ($allAlokasiHonor->isEmpty()) {
            return "Tidak ada data alokasi honor yang memiliki nomor kontrak pada periode yang dipilih.";
        }

        $tanggalPengajuan = Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::getPpkByDate($tanggalPengajuan);

        // Pisahkan alokasi honor menjadi 2 kelompok: SURVEI (gabungan) dan SENSUS (mandiri per kegiatan)
        $surveiAlokasi = $allAlokasiHonor->filter(fn($a) => $a->honor?->kegiatanManmit?->jenis_kegiatan !== 'SENSUS');
        $sensusAlokasiGroups = $allAlokasiHonor
            ->filter(fn($a) => $a->honor?->kegiatanManmit?->jenis_kegiatan === 'SENSUS')
            ->groupBy('honor.kegiatan_manmit_id');

        $renderedHtml = '';

        // 1. Render SURVEI (Gabungan seluruh kegiatan survei)
        if ($surveiAlokasi->isNotEmpty()) {
            $renderedHtml .= view('kontrak.pdf', [
                'alokasiHonor' => $surveiAlokasi,
                'tahun' => $tanggalPengajuan->year,
                'bulan' => $tanggalPengajuan->month,
                'ppk' => $ppk,
                'id_honor' => $id_honor_request,
                'id_kegiatan_manmit' => $idKegiatanManmit,
                'kegiatan' => $kegiatanRequested && !$isSensusOnly ? $kegiatanRequested : null,
            ])->render();
        }

        // 2. Render SENSUS (Terpisah per kegiatan sensus)
        foreach ($sensusAlokasiGroups as $kegManmitId => $sensusGroup) {
            $kegiatanSensus = \App\Models\KegiatanManmit::find($kegManmitId);
            $template = ($kegiatanSensus?->jenis_kegiatan === 'SENSUS' && view()->exists('kontrak.pdf_sensus'))
                ? 'kontrak.pdf_sensus'
                : 'kontrak.pdf';

            $renderedHtml .= view($template, [
                'alokasiHonor' => $sensusGroup,
                'tahun' => $tanggalPengajuan->year,
                'bulan' => $tanggalPengajuan->month,
                'ppk' => $ppk,
                'id_honor' => $id_honor_request,
                'id_kegiatan_manmit' => $kegManmitId,
                'kegiatan' => $kegiatanSensus,
            ])->render();
        }

        return response($renderedHtml);
    }
    public function cetakBast() {
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

        // **FILTER UTAMA**: filter berdasarkan ID Kegiatan Manmit + bulan dari tanggal_akhir_kegiatan
        $mitraIdRequest = request('mitra_id');

        if ($id_kegiatan_manmit_request) {
            $alokasiHonorQuery->whereHas('honor', function ($query) use ($id_kegiatan_manmit_request, $tahun, $bulan) {
                $query->where('kegiatan_manmit_id', $id_kegiatan_manmit_request)
                      // Filter bulan dari honor.tanggal_akhir_kegiatan (sumber kebenaran bulan kontrak)
                      ->whereYear('tanggal_akhir_kegiatan', $tahun)
                      ->whereMonth('tanggal_akhir_kegiatan', $bulan);
            });
        } else if (!$mitraIdRequest) {
            // Fallback jika tidak ada id_kegiatan_manmit dan mitra_id
            $alokasiHonorQuery->whereHas('honor', function ($q) use ($tahun, $bulan) {
                $q->whereYear('tanggal_akhir_kegiatan', $tahun)
                  ->whereMonth('tanggal_akhir_kegiatan', $bulan);
            });
        }

        if ($mitraIdRequest) {
            $alokasiHonorQuery->where('mitra_id', $mitraIdRequest)
                ->whereHas('honor', function($q) use ($tahun, $bulan) {
                    $q->whereYear('tanggal_akhir_kegiatan', $tahun)
                      ->whereMonth('tanggal_akhir_kegiatan', $bulan);
                });
        }

        // Opsi id_honor tetap dipertahankan jika diperlukan
        $id_honor_request = request('id_honor') ?? null;
        if ($id_honor_request) {
            $alokasiHonorQuery->where('honor_id', $id_honor_request);
        }

        $alokasiHonor = $alokasiHonorQuery->get();
        // --- AKHIR LOGIKA BARU YANG DIPERBAIKI ---

        // Tentukan PPK dari tanggal_nomor BAST aktual (bukan dari URL param tahun-bulan).
        // Ini memastikan PPK selalu benar sesuai tanggal dokumen, apapun URL yang dikirim.
        // Fallback ke tahun-bulan dari URL jika tidak ada record BAST yang dimuat.
        $tanggalBastAktual = $alokasiHonor->first()?->bast?->tanggal_nomor;
        $tanggalUntukPpk   = $tanggalBastAktual
            ? Carbon::parse($tanggalBastAktual)
            : Carbon::parse("$tahun-$bulan-01");
        $ppk = Pegawai::getPpkByDate($tanggalUntukPpk);

        return view('bast.pdf', [
            'alokasiHonor'       => $alokasiHonor,
            'tahun'              => (int) $tahun,
            'bulan'              => (int) $bulan,
            'id_honor'           => $id_honor_request,
            'ppk'                => $ppk,
            'id_kegiatan_manmit' => $id_kegiatan_manmit_request,
        ]);
    }
}
