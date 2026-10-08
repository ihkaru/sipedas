<?php

namespace App\Services\SuratTugas;

use App\Models\Kegiatan;
use App\Models\MasterSls;
use App\Models\Mitra;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\Plh;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class SuratTugasValidationService
{
    /**
     * Normalisasi payload mentah menjadi format seragam standar penugasan.
     */
    public function normalize(array $data): array
    {
        // 1. Jenis Surat Tugas
        $jenisSurat = $data['jenis_surat_tugas'] ?? Constants::NON_SPPD;
        $jenisSuratUpper = strtoupper(trim((string) $jenisSurat));
        $jenisSuratMap = [
            'NON_SPPD' => Constants::NON_SPPD,
            'PERJALAN_DINAS_DALAM_KOTA' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'DALAM_KOTA' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'PDK' => Constants::PERJALAN_DINAS_DALAM_KOTA,
            'PERJALANAN_DINAS_LUAR_KOTA' => Constants::PERJALANAN_DINAS_LUAR_KOTA,
            'LUAR_KOTA' => Constants::PERJALANAN_DINAS_LUAR_KOTA,
            'PDLK' => Constants::PERJALANAN_DINAS_LUAR_KOTA,
            'PERJALANAN_DINAS_PAKET_MEETING' => Constants::PERJALANAN_DINAS_PAKET_MEETING,
            'PAKET_MEETING' => Constants::PERJALANAN_DINAS_PAKET_MEETING,
            'MEETING' => Constants::PERJALANAN_DINAS_PAKET_MEETING,
        ];
        $data['jenis_surat_tugas'] = $jenisSuratMap[$jenisSuratUpper] ?? $jenisSurat;

        // 2. Normalisasi Personil (Pegawai & Mitra)
        $nips = [];
        if (isset($data['nips']) && is_array($data['nips'])) {
            $nips = array_values(array_filter(array_map('trim', $data['nips'])));
        } elseif (!empty($data['nip'])) {
            $nips = is_array($data['nip']) ? array_values(array_filter(array_map('trim', $data['nip']))) : [trim((string) $data['nip'])];
        }

        $mitras = [];
        if (isset($data['mitras']) && is_array($data['mitras'])) {
            $mitras = array_values(array_filter(array_map('trim', $data['mitras'])));
        } elseif (!empty($data['mitra'])) {
            $mitras = is_array($data['mitra']) ? array_values(array_filter(array_map('trim', $data['mitra']))) : [trim((string) $data['mitra'])];
        } elseif (!empty($data['id_sobat'])) {
            $mitras = is_array($data['id_sobat']) ? array_values(array_filter(array_map('trim', $data['id_sobat']))) : [trim((string) $data['id_sobat'])];
        } elseif (!empty($data['mitra_ids']) && is_array($data['mitra_ids'])) {
            $mitraRecords = Mitra::whereIn('id', $data['mitra_ids'])->pluck('id_sobat')->toArray();
            $mitras = array_values(array_filter($mitraRecords));
        } elseif (!empty($data['mitra_id'])) {
            $mitraRecord = Mitra::find($data['mitra_id']);
            if ($mitraRecord && $mitraRecord->id_sobat) {
                $mitras = [$mitraRecord->id_sobat];
            }
        }

        $data['nips'] = $nips;
        $data['mitras'] = $mitras;

        // 3. Normalisasi Jenis Peserta
        if (!empty($data['jenis_peserta'])) {
            $jp = strtoupper(trim((string) $data['jenis_peserta']));
            $jpMap = [
                'PEGAWAI' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
                'JENIS_PESERTA_SURAT_TUGAS_PEGAWAI' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI,
                'MITRA' => Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA,
                'JENIS_PESERTA_SURAT_TUGAS_MITRA' => Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA,
                'PEGAWAI_DAN_MITRA' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA,
                'PEGAWAI_MITRA' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA,
                'JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA' => Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA,
            ];
            $data['jenis_peserta'] = $jpMap[$jp] ?? $data['jenis_peserta'];
        } else {
            // Auto-detect berdasarkan personil yang diberikan
            if (!empty($nips) && !empty($mitras)) {
                $data['jenis_peserta'] = Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA;
            } elseif (!empty($mitras)) {
                $data['jenis_peserta'] = Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA;
            } else {
                $data['jenis_peserta'] = Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI;
            }
        }

        // 4. Normalisasi Level Tujuan Penugasan
        $level = $data['level_tujuan_penugasan'] ?? Constants::LEVEL_PENUGASAN_TANPA_LOKASI;
        $levelStr = strtoupper(trim((string) $level));
        $levelMap = [
            '0' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'TANPA_LOKASI' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            'LEVEL_PENUGASAN_TANPA_LOKASI' => Constants::LEVEL_PENUGASAN_TANPA_LOKASI,
            '1' => Constants::LEVEL_PENUGASAN_NAMA_TEMPAT,
            'NAMA_TEMPAT' => Constants::LEVEL_PENUGASAN_NAMA_TEMPAT,
            'LEVEL_PENUGASAN_NAMA_TEMPAT' => Constants::LEVEL_PENUGASAN_NAMA_TEMPAT,
            '2' => Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
            'KABUPATEN_KOTA' => Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
            'KABKOT' => Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
            'LEVEL_PENUGASAN_KABUPATEN_KOTA' => Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA,
            '3' => Constants::LEVEL_PENUGASAN_KECAMATAN,
            'KECAMATAN' => Constants::LEVEL_PENUGASAN_KECAMATAN,
            'LEVEL_PENUGASAN_KECAMATAN' => Constants::LEVEL_PENUGASAN_KECAMATAN,
            '4' => Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
            'DESA_KELURAHAN' => Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
            'DESA' => Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
            'LEVEL_PENUGASAN_DESA_KELURAHAN' => Constants::LEVEL_PENUGASAN_DESA_KELURAHAN,
        ];
        $data['level_tujuan_penugasan'] = $levelMap[$levelStr] ?? $level;

        // 5. Normalisasi Array Lokasi (Prov, Kabkot, Kec, Desa)
        $data['prov_ids'] = $this->ensureArray($data, ['prov_ids', 'prov_id']);
        $data['kabkot_ids'] = $this->ensureArray($data, ['kabkot_ids', 'kabkot_id']);
        $data['kecamatan_ids'] = $this->ensureArray($data, ['kecamatan_ids', 'kecamatan_id', 'kec_ids', 'kec_id']);
        $data['desa_kel_ids'] = $this->ensureArray($data, ['desa_kel_ids', 'desa_kel_id']);

        // 6. Tanggal Pengajuan Tugas (default: hari ini)
        if (empty($data['tgl_pengajuan_tugas'])) {
            $data['tgl_pengajuan_tugas'] = now()->toDateString();
        } else {
            $data['tgl_pengajuan_tugas'] = Carbon::parse($data['tgl_pengajuan_tugas'])->toDateString();
        }

        // 7. Normalisasi Hari Jalan Tambahan
        $data['tbh_hari_jalan_awal'] = isset($data['tbh_hari_jalan_awal']) ? (int) $data['tbh_hari_jalan_awal'] : 0;
        $data['tbh_hari_jalan_akhir'] = isset($data['tbh_hari_jalan_akhir']) ? (int) $data['tbh_hari_jalan_akhir'] : 0;

        // 8. Normalisasi Transportasi
        if (!empty($data['transportasi'])) {
            $tr = strtoupper(trim((string) $data['transportasi']));
            $trMap = [
                '-' => Constants::NON_TRANSPORTASI,
                'NONE' => Constants::NON_TRANSPORTASI,
                'NON_TRANSPORTASI' => Constants::NON_TRANSPORTASI,
                'UMUM' => Constants::TRANSPORTASI_KENDARAAN_UMUM,
                'KENDARAAN_UMUM' => Constants::TRANSPORTASI_KENDARAAN_UMUM,
                'TRANSPORTASI_KENDARAAN_UMUM' => Constants::TRANSPORTASI_KENDARAAN_UMUM,
                'DINAS' => Constants::TRANSPORTASI_KENDARAAN_DINAS,
                'KENDARAAN_DINAS' => Constants::TRANSPORTASI_KENDARAAN_DINAS,
                'TRANSPORTASI_KENDARAAN_DINAS' => Constants::TRANSPORTASI_KENDARAAN_DINAS,
                'PRIBADI' => Constants::TRANSPORTASI_KENDARAAN_PRIBADI,
                'KENDARAAN_PRIBADI' => Constants::TRANSPORTASI_KENDARAAN_PRIBADI,
                'TRANSPORTASI_KENDARAAN_PRIBADI' => Constants::TRANSPORTASI_KENDARAAN_PRIBADI,
            ];
            $data['transportasi'] = $trMap[$tr] ?? $data['transportasi'];
        }

        return $data;
    }

    /**
     * Konversi parameter input (array atau string tunggal) menjadi array string.
     */
    private function ensureArray(array $data, array $keys): ?array
    {
        foreach ($keys as $k) {
            if (isset($data[$k])) {
                $val = $data[$k];
                if (is_array($val)) {
                    $cleaned = array_values(array_filter(array_map('strval', $val)));
                    return !empty($cleaned) ? $cleaned : null;
                }
                if ($val !== '' && $val !== null) {
                    return [(string) $val];
                }
            }
        }
        return null;
    }

    /**
     * Validasi bisnis lengkap terhadap data penugasan.
     * Mengembalikan data yang tervalidasi dan ternormalisasi atau melempar ValidationException.
     *
     * @throws ValidationException
     */
    public function validate(array $raw): array
    {
        $data = $this->normalize($raw);
        $check = $this->checkEligibility($data);

        if (!$check['eligible']) {
            $firstField = array_key_first($check['errors']) ?? 'general';
            throw ValidationException::withMessages($check['errors'])->errorBag('default');
        }

        return $data;
    }

    /**
     * Pemeriksaan kelayakan pre-flight dry-run (Agent-Native).
     * Tidak memutasi database, mengembalikan status kelayakan, error rincian, dan remediasi aksi.
     */
    public function checkEligibility(array $raw): array
    {
        $data = $this->normalize($raw);
        $errors = [];
        $remediations = [];
        $conflicts = [];

        // 1. Validasi Jenis Surat Tugas
        if (!in_array($data['jenis_surat_tugas'], array_keys(Constants::JENIS_SURAT_TUGAS_OPTIONS))) {
            $errors['jenis_surat_tugas'] = ["Jenis surat tugas tidak valid. Pilihan valid: " . implode(', ', array_keys(Constants::JENIS_SURAT_TUGAS_OPTIONS))];
            $remediations['jenis_surat_tugas'] = "Gunakan salah satu dari: NON_SPPD, PERJALAN_DINAS_DALAM_KOTA, PERJALANAN_DINAS_LUAR_KOTA, PERJALANAN_DINAS_PAKET_MEETING.";
        }

        // 2. Validasi Kegiatan
        $kegiatan = null;
        if (empty($data['kegiatan_id'])) {
            $errors['kegiatan_id'] = ["Kegiatan (kegiatan_id) wajib dipilih."];
            $remediations['kegiatan_id'] = "Pilih kegiatan yang terdaftar melalui GET /api/v1/kegiatan-manmit.";
        } else {
            $kegiatan = Kegiatan::find($data['kegiatan_id']);
            if (!$kegiatan) {
                $errors['kegiatan_id'] = ["Kegiatan dengan ID '{$data['kegiatan_id']}' tidak ditemukan."];
                $remediations['kegiatan_id'] = "Periksa kembali ID kegiatan melalui endpoint GET /api/v1/kegiatan-manmit.";
            }
        }

        // 3. Validasi Personil
        $jenisPeserta = $data['jenis_peserta'];
        $nips = $data['nips'];
        $mitras = $data['mitras'];

        if ($jenisPeserta === Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI || $jenisPeserta === Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA) {
            if (empty($nips)) {
                $errors['nips'] = ["Pegawai yang ditugaskan (nips) wajib diisi minimal 1 pegawai."];
                $remediations['nips'] = "Cantumkan NIP pegawai yang aktif pada array 'nips'.";
            } else {
                $existingNips = Pegawai::whereIn('nip', $nips)->pluck('nip')->toArray();
                $diff = array_diff($nips, $existingNips);
                if (!empty($diff)) {
                    $errors['nips'] = ["NIP pegawai berikut tidak ditemukan di sistem: " . implode(', ', $diff)];
                    $remediations['nips'] = "Gunakan NIP valid yang terdaftar pada sistem (cek GET /api/v1/pegawais).";
                }
            }
        }

        if ($jenisPeserta === Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA || $jenisPeserta === Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI_MITRA) {
            if (empty($mitras)) {
                $errors['mitras'] = ["Mitra yang ditugaskan (mitras) wajib diisi minimal 1 mitra."];
                $remediations['mitras'] = "Cantumkan ID Sobat mitra pada array 'mitras'.";
            } else {
                $existingMitras = Mitra::whereIn('id_sobat', $mitras)->pluck('id_sobat')->toArray();
                $diff = array_diff($mitras, $existingMitras);
                if (!empty($diff)) {
                    $errors['mitras'] = ["ID Sobat mitra berikut tidak ditemukan di sistem: " . implode(', ', $diff)];
                    $remediations['mitras'] = "Gunakan ID Sobat mitra yang terdaftar pada sistem (cek GET /api/v1/mitras).";
                }
            }
        }

        // 4. Validasi Tanggal Pengajuan
        $tglPengajuan = Carbon::parse($data['tgl_pengajuan_tugas']);
        if ($tglPengajuan->endOfDay()->isBefore(now()->startOfDay()->subYears(2))) {
            $errors['tgl_pengajuan_tugas'] = ["Tanggal pengajuan tugas terlalu lampau."];
        }

        // 5. Validasi Tanggal Mulai dan Selesai
        $tglMulai = null;
        $tglAkhir = null;

        if (empty($data['tgl_mulai_tugas'])) {
            $errors['tgl_mulai_tugas'] = ["Tanggal mulai tugas (tgl_mulai_tugas) wajib diisi."];
        } else {
            try {
                $tglMulai = Carbon::parse($data['tgl_mulai_tugas']);
            } catch (\Throwable $e) {
                $errors['tgl_mulai_tugas'] = ["Format tanggal mulai tugas tidak valid."];
            }
        }

        if (empty($data['tgl_akhir_tugas'])) {
            $errors['tgl_akhir_tugas'] = ["Tanggal selesai tugas (tgl_akhir_tugas) wajib diisi."];
        } else {
            try {
                $tglAkhir = Carbon::parse($data['tgl_akhir_tugas']);
            } catch (\Throwable $e) {
                $errors['tgl_akhir_tugas'] = ["Format tanggal selesai tugas tidak valid."];
            }
        }

        if ($tglMulai && $tglAkhir) {
            if ($tglMulai->isAfter($tglAkhir)) {
                $errors['tgl_mulai_tugas'] = ["Tanggal mulai tugas ({$tglMulai->toDateString()}) tidak boleh melebihi tanggal selesai tugas ({$tglAkhir->toDateString()})."];
                $remediations['tgl_mulai_tugas'] = "Pastikan rentang tanggal mulai <= tanggal selesai.";
            }

            // Validasi batasan tanggal perjadin dari Kegiatan jika didefinisikan
            if ($kegiatan) {
                if ($kegiatan->tgl_awal_perjadin && $tglMulai->toDateString() < Carbon::parse($kegiatan->tgl_awal_perjadin)->toDateString()) {
                    $awal = Carbon::parse($kegiatan->tgl_awal_perjadin)->toDateString();
                    $errors['tgl_mulai_tugas'] = ["Tanggal mulai tugas ({$tglMulai->toDateString()}) mendahului batas awal perjadin kegiatan '{$kegiatan->nama}' ({$awal})."];
                    $remediations['tgl_mulai_tugas'] = "Ubah tanggal mulai tugas minimal ke {$awal} atau perbarui jadwal kegiatan terkait.";
                }

                if ($kegiatan->tgl_akhir_perjadin && $tglAkhir->toDateString() > Carbon::parse($kegiatan->tgl_akhir_perjadin)->toDateString()) {
                    $akhir = Carbon::parse($kegiatan->tgl_akhir_perjadin)->toDateString();
                    $errors['tgl_akhir_tugas'] = ["Tanggal selesai tugas ({$tglAkhir->toDateString()}) melampaui batas akhir perjadin kegiatan '{$kegiatan->nama}' ({$akhir})."];
                    $remediations['tgl_akhir_tugas'] = "Ubah tanggal selesai tugas maksimal ke {$akhir} atau perpanjang jadwal kegiatan terkait.";
                }
            }

            // 6. Validasi Khusus Perjalanan Dinas (SPPD)
            $isPerjadin = ($data['jenis_surat_tugas'] !== Constants::NON_SPPD);
            if ($isPerjadin) {
                // a. Cek Hari Libur / Tanggal Merah untuk tanggal mulai tugas
                if (TanggalMerah::isLibur($tglMulai)) {
                    $errors['tgl_mulai_tugas'] = ["Tanggal mulai penugasan ({$tglMulai->toDateString()}) jatuh pada hari libur atau akhir pekan."];
                    $remediations['tgl_mulai_tugas'] = "Untuk perjalanan dinas (SPPD), tanggal mulai penugasan harus jatuh pada hari kerja, atau aktifkan setting 'allow_weekend_travel'.";
                }

                // b. Cek Bentrok Jadwal Perjadin untuk Pegawai yang ditugaskan
                if (!empty($nips)) {
                    $requestedDates = Penugasan::generateDateRange($tglMulai, $tglAkhir);

                    // Ambil seluruh penugasan SPPD aktif yang bersinggungan langsung dengan rentang tanggal yang diminta
                    $overlappingPenugasans = Penugasan::whereIn('nip', $nips)
                        ->whereNotNull('surat_perjadin_id')
                        ->whereDate('tgl_mulai_tugas', '<=', $tglAkhir->toDateString())
                        ->whereDate('tgl_akhir_tugas', '>=', $tglMulai->toDateString())
                        ->whereHas('riwayatPengajuan', function ($q) {
                            $q->whereIn('status', [
                                Constants::STATUS_PENGAJUAN_DIKIRIM,
                                Constants::STATUS_PENGAJUAN_DISETUJUI,
                                Constants::STATUS_PENGAJUAN_DICETAK,
                                Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                                Constants::STATUS_PENGAJUAN_DICAIRKAN,
                                Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                            ]);
                        })->get();

                    $overlapDates = [];
                    foreach ($overlappingPenugasans as $op) {
                        $opDates = Penugasan::generateDateRange(Carbon::parse($op->tgl_mulai_tugas), Carbon::parse($op->tgl_akhir_tugas));
                        $overlapDates = array_merge($overlapDates, array_intersect($requestedDates, $opDates));
                    }
                    $overlapDates = array_values(array_unique($overlapDates));

                    if (!empty($overlapDates)) {
                        $errors['jadwal_bentrok'] = ["Terdapat personil yang sudah memiliki agenda Perjalanan Dinas (SPPD) lain pada tanggal: " . implode(', ', $overlapDates)];
                        $remediations['jadwal_bentrok'] = "Pilih rentang tanggal lain yang tidak tumpang-tindih atau gunakan jenis penugasan Non-SPPD.";
                        $conflicts['overlapping_dates'] = $overlapDates;

                        // Rinci pegawai mana yang bentrok
                        foreach ($nips as $nipCheck) {
                            $pegawaiOverlap = $overlappingPenugasans->where('nip', $nipCheck);
                            if ($pegawaiOverlap->isNotEmpty()) {
                                $pegDates = [];
                                foreach ($pegawaiOverlap as $pop) {
                                    $popDates = Penugasan::generateDateRange(Carbon::parse($pop->tgl_mulai_tugas), Carbon::parse($pop->tgl_akhir_tugas));
                                    $pegDates = array_merge($pegDates, array_intersect($requestedDates, $popDates));
                                }
                                $peg = Pegawai::where('nip', $nipCheck)->first();
                                $conflicts['pegawai'][] = [
                                    'nip' => $nipCheck,
                                    'nama' => $peg?->nama ?? $nipCheck,
                                    'bentrok_dates' => array_values(array_unique($pegDates)),
                                ];
                            }
                        }
                    }
                }
            }
        }

        // 7. Validasi Level dan Lokasi Tujuan
        $levelTujuan = $data['level_tujuan_penugasan'];
        if ($levelTujuan === Constants::LEVEL_PENUGASAN_NAMA_TEMPAT) {
            if (empty($data['nama_tempat_tujuan'])) {
                $errors['nama_tempat_tujuan'] = ["Nama lokasi tujuan (nama_tempat_tujuan) wajib diisi untuk level tujuan 'Nama Tempat'."];
                $remediations['nama_tempat_tujuan'] = "Contoh: 'Kantor BPS Kalimantan Barat', 'Balai Desa Wajok Hulu', 'Hotel Orchardz Pontianak'.";
            }
        } elseif ($levelTujuan === Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA) {
            if (empty($data['prov_ids']) || empty($data['kabkot_ids'])) {
                $errors['kabkot_ids'] = ["Provinsi dan Kabupaten/Kota tujuan wajib dipilih untuk level tujuan 'Kabupaten/Kota'."];
            }
        } elseif ($levelTujuan === Constants::LEVEL_PENUGASAN_KECAMATAN) {
            if (empty($data['prov_ids']) || empty($data['kabkot_ids']) || empty($data['kecamatan_ids'])) {
                $errors['kecamatan_ids'] = ["Provinsi, Kabupaten/Kota, dan Kecamatan tujuan wajib dipilih untuk level tujuan 'Kecamatan'."];
            }
        } elseif ($levelTujuan === Constants::LEVEL_PENUGASAN_DESA_KELURAHAN) {
            if (empty($data['prov_ids']) || empty($data['kabkot_ids']) || empty($data['kecamatan_ids']) || empty($data['desa_kel_ids'])) {
                $errors['desa_kel_ids'] = ["Provinsi, Kabupaten/Kota, Kecamatan, dan Desa/Kelurahan tujuan wajib dipilih untuk level tujuan 'Desa/Kelurahan'."];
            }
        }

        // 8. Validasi Transportasi
        if ($data['jenis_surat_tugas'] !== Constants::NON_SPPD && $levelTujuan !== Constants::LEVEL_PENUGASAN_TANPA_LOKASI) {
            if (empty($data['transportasi'])) {
                $errors['transportasi'] = ["Moda transportasi wajib dipilih untuk perjalanan dinas berlokasi."];
                $remediations['transportasi'] = "Pilih salah satu: TRANSPORTASI_KENDARAAN_DINAS, TRANSPORTASI_KENDARAAN_UMUM, atau TRANSPORTASI_KENDARAAN_PRIBADI.";
            }
        }

        // 9. Estimasi Penyetuju (Approver / Plh)
        $approver = null;
        if (!empty($data['tgl_mulai_tugas'])) {
            try {
                $pegawaiPlh = Plh::getApprover($nips, Carbon::parse($data['tgl_mulai_tugas'])->toDateTimeString(), true);
                if ($pegawaiPlh) {
                    $approver = [
                        'nip' => $pegawaiPlh->nip,
                        'nama' => $pegawaiPlh->nama,
                        'jabatan' => $pegawaiPlh->jabatan,
                    ];
                }
            } catch (\Throwable $e) {
                // Abaikan exception approver pada dry-run check
            }
        }

        // 10. Preview Dokumen yang akan diterbitkan
        $dokumenPreview = [
            'surat_tugas' => [
                'akan_diterbitkan' => true,
                'jenis' => $data['jenis_surat_tugas'],
                'mode' => count($nips) + count($mitras) > 1 ? 'Surat Tugas Bersama (Tim)' : 'Surat Tugas Tunggal',
            ],
            'surat_perjalanan_dinas' => [
                'akan_diterbitkan' => ($data['jenis_surat_tugas'] !== Constants::NON_SPPD),
                'keterangan' => ($data['jenis_surat_tugas'] === Constants::NON_SPPD) 
                    ? 'Non-SPPD (Tidak menerbitkan lembar SPD)' 
                    : 'Menerbitkan Nomor SPD otomatis saat disetujui',
            ],
        ];

        return [
            'eligible' => empty($errors),
            'errors' => $errors,
            'remediations' => $remediations,
            'conflicts' => $conflicts,
            'approver' => $approver,
            'preview' => [
                'jenis_surat_tugas' => $data['jenis_surat_tugas'],
                'jenis_peserta' => $data['jenis_peserta'],
                'total_pegawai' => count($nips),
                'total_mitra' => count($mitras),
                'total_personil' => count($nips) + count($mitras),
                'kegiatan' => $kegiatan ? [
                    'id' => $kegiatan->id,
                    'nama' => $kegiatan->nama,
                ] : null,
                'tgl_mulai_tugas' => $data['tgl_mulai_tugas'] ?? null,
                'tgl_akhir_tugas' => $data['tgl_akhir_tugas'] ?? null,
                'level_tujuan' => $data['level_tujuan_penugasan'],
                'dokumen' => $dokumenPreview,
            ],
        ];
    }
}
