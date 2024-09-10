<?php

namespace App\Models;

use App\DTO\PenugasanCreation;
use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Collection;
use PHPUnit\TextUI\Configuration\Constant;

class Penugasan extends Model
{
    use HasFactory;
    protected $guarded = [];
    public static Collection $masterSls;
    protected ?Pegawai $pegawaiPlh = null;
    protected ?Pegawai $plhSaatMulaiPerjalanan = null;

    protected function casts(): array
    {
        return [
            'plh_id' => 'string',
        ];
    }

    protected function pemberiPerintah(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Plh::getApprover([$attributes['nip']], $attributes['tgl_pengajuan_tugas'], true),
        );
    }
    protected function penandaTanganHariPertama(): Attribute
    {
        return $this->pemberiPerintah();
    }

    protected function isMitra(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => (bool) $attributes['id_sobat'],
        );
    }

    protected function jenisSurat(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Constants::JENIS_SURAT_TUGAS_OPTIONS[$attributes["jenis_surat_tugas"]],
        );
    }
    protected function jenisTransportasi(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => Constants::JENIS_TRANSPORTASI_OPTIONS[$attributes["transportasi"]],
        );
    }
    protected function lamaPerjadin(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return ((int) Carbon::parse($attributes['tgl_mulai_tugas'])->diffInDays(Carbon::parse($attributes['tgl_akhir_tugas']))) + 1;
            },
        );
    }
    protected function jenisPerjadin(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!$attributes['jenis_surat_tugas']) return null;
                return Constants::JENIS_SURAT_TUGAS_OPTIONS[$attributes['jenis_surat_tugas']];
            },
        );
    }
    protected function tujuanPenugasan(): Attribute
    {
        $masterSls = self::$masterSls ??= MasterSls::get();
        $tujuanSuratTugas = $this->tujuanSuratTugas;
        return Attribute::make(get: function (mixed $value, array $attributes) use ($masterSls, $tujuanSuratTugas) {
            if ($attributes['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_NAMA_TEMPAT) return ucwords(strtolower($tujuanSuratTugas->first()->nama_tempat_tujuan));
            return TujuanSuratTugas::combinerTujuan($tujuanSuratTugas, $masterSls);
        });
    }
    protected function tertugas(): Attribute
    {
        $tertugas = $this->pegawai?->nama ?? $this->mitra?->nama_1;
        return Attribute::make(get: function (mixed $value, array $attributes) use ($tertugas) {
            return $tertugas;
        });
    }
    protected function jenisPetugas(): Attribute
    {
        $jenisPetugas = $this->isMitra ? Constants::JABATAN_MITRA : Constants::JABATAN_PEGAWAI;
        return Attribute::make(get: function (mixed $value, array $attributes) use ($jenisPetugas) {
            return $jenisPetugas;
        });
    }
    protected function tglPerjadin(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (Carbon::parse($attributes['tgl_mulai_tugas'])->toDateString() == Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString())
                    return Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString();
                return Carbon::parse($attributes['tgl_mulai_tugas'])->toDateString() . " - " . Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString();
            },
        );
    }

    public function suratTugas()
    {
        return $this->belongsTo(NomorSurat::class, "surat_tugas_id", "id");
    }
    public function suratPerjadin()
    {
        return $this->belongsTo(NomorSurat::class, "surat_perjadin_id", "id");
    }
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, "nip", "nip");
    }
    public function pengaju()
    {
        return $this->belongsTo(Pegawai::class, "nip_pengaju", "nip");
    }
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, "id_sobat", "id_sobat");
    }
    public function plhSesuai()
    {
        if ($this->pegawaiPlh) return $this->pegawaiPlh;
        $this->pegawaiPlh = Plh::getPlhAktif(Carbon::parse($this->tgl_pengajuan_tugas), returnPegawai: true);
        return $this->pegawaiPlh;
    }
    public function plhSaatMulaiPerjalanan()
    {
        if ($this->plhSaatMulaiPerjalanan) return $this->plhSaatMulaiPerjalanan;
        $this->plhSaatMulaiPerjalanan = Plh::getPlhAktif(Carbon::parse($this->tgl_mulai_tugas), returnPegawai: true);
        return $this->plhSaatMulaiPerjalanan;
    }
    public function plh()
    {
        return $this->belongsTo(Pegawai::class, "plh_id", "nip");
    }
    public function riwayatPengajuan()
    {
        return $this->hasOne(RiwayatPengajuan::class, "penugasan_id", "id");
    }
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, "kegiatan_id", "id");
    }
    public function satuSurat()
    {
        return $this->hasMany(Penugasan::class, "surat_tugas_id", "surat_tugas_id");
    }
    public function tujuanSuratTugas()
    {
        return $this->hasMany(TujuanSuratTugas::class);
    }

    public function suratTugasBersamaDisetujui(array $with = null)
    {
        $query = self::query();
        $surat_tugas_id = $this->surat_tugas_id;
        if (!$surat_tugas_id) return collect([]);
        if ($with) $query = $query->with($with);
        return $query->where("surat_tugas_id", $surat_tugas_id)->whereHas('riwayatPengajuan', function ($q) {
            return $q->where(function ($q) {
                $q->where('status', Constants::STATUS_PENGAJUAN_DISETUJUI)
                    ->orWhere('status', Constants::STATUS_PENGAJUAN_DICETAK)
                    ->orWhere('status', Constants::STATUS_PENGAJUAN_DIKUMPULKAN)
                    ->orWhere('status', Constants::STATUS_PENGAJUAN_DICAIRKAN);
            });
        })->get();
    }
    public function isSuratTugasBersama()
    {
        $query = self::query();
        $surat_tugas_id = $this->surat_tugas_id;
        if (!$surat_tugas_id) return collect([]);
        return $query->where("surat_tugas_id", $surat_tugas_id)->count() > 1;
    }
    public function satuGrupPengajuan()
    {
        return $this->hasMany(Penugasan::class, "grup_id", "grup_id");
    }
    public static function ajukan(array $data)
    {
        $now = now()->toDateTimeString();
        $res = 0;
        $pegawaiPlh = Plh::getApprover($data["nips"] ?? null, Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(), true);
        $grupId = self::getGrupId();
        $data["mitras"] = $data["mitras"] ?? [];
        $data["nips"] = $data["nips"] ?? [];
        $data["prov_ids"] = self::dataToArray($data, "prov_ids");
        $data["kabkot_ids"] = self::dataToArray($data, "kabkot_ids");
        $data["kecamatan_ids"] = self::dataToArray($data, "kecamatan_ids");
        $data["desa_kel_ids"] = self::dataToArray($data, "desa_kel_ids");
        foreach ($data["nips"] as $n) {
            $pengajuan = self::create([
                "nip" => $n,
                "kegiatan_id" => $data["kegiatan_id"],
                "nip_pengaju" => auth()?->user()?->pegawai?->nip ?? $data["nip_pengaju"],
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "tgl_mulai_tugas" => Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),
                "tgl_akhir_tugas" => Carbon::parse($data["tgl_akhir_tugas"])->toDateTimeString(),
                "tbh_hari_jalan_awal" => $data["tbh_hari_jalan_awal"] ?? null,
                "tbh_hari_jalan_akhir" => $data["tbh_hari_jalan_akhir"] ?? null,
                "tgl_pengajuan_tugas" => $data["tgl_pengajuan_tugas"] ?? self::getNearestPemberiTugasDate(now() >= Carbon::parse($data["tgl_mulai_tugas"]) ? Carbon::parse($data["tgl_mulai_tugas"])->toDateString() : now(), Carbon::parse($data["tgl_mulai_tugas"])->toDateString(), $data["nips"]),
                "jenis_peserta" => $data["jenis_peserta"] ?? null,
                "grup_id" => $grupId,
                "jenis_surat_tugas" => $data["jenis_surat_tugas"],
                "plh_id" => $pegawaiPlh->nip,
                "transportasi" => $data["transportasi"] ?? null,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            TujuanSuratTugas::ajukan($data, $pengajuan->id);
            if ($pengajuan) $res += 1;
        }
        foreach ($data["mitras"] as $n) {
            $pengajuan = self::create([
                "id_sobat" => $n,
                "kegiatan_id" => $data["kegiatan_id"],
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "nip_pengaju" => auth()?->user()?->pegawai?->nip ?? $data["nip_pengaju"],
                "tgl_mulai_tugas" => Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),
                "tgl_akhir_tugas" => Carbon::parse($data["tgl_akhir_tugas"])->toDateTimeString(),
                "nama_tempat_tujuan" => $data["nama_tempat_tujuan"] ?? null,
                "tbh_hari_jalan_awal" => $data["tbh_hari_jalan_awal"] ?? null,
                "tbh_hari_jalan_akhir" => $data["tbh_hari_jalan_akhir"] ?? null,
                "tgl_pengajuan_tugas" => $data["tgl_pengajuan_tugas"] ?? self::getNearestPemberiTugasDate(now() >= Carbon::parse($data["tgl_mulai_tugas"]) ? Carbon::parse($data["tgl_mulai_tugas"])->toDateString() : now(), Carbon::parse($data["tgl_mulai_tugas"])->toDateString(), $data["nips"]),
                "grup_id" => $grupId,
                "jenis_peserta" => $data["jenis_peserta"] ?? null,
                "jenis_surat_tugas" => $data["jenis_surat_tugas"],
                "plh_id" => $pegawaiPlh?->nip ?? null,
                "transportasi" => $data["transportasi"] ?? null,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            TujuanSuratTugas::ajukan($data, $pengajuan->id);
            if ($pengajuan) $res += 1;
            if ($pengajuan->jenis_surat_tugas == Constants::NON_SPPD) $pengajuan->setujui(checkRole: false);
        }
        if ($res == count($data["nips"]) + count($data["mitras"])) return true;
        return null;
    }

    public static function perluPerbaikan($data, bool $checkRole = false)
    {
        $res = 0;
        $pengajuan = self::with("riwayatPengajuan")->find($data['id']);
        if (!$pengajuan->canPerluPerbaikan($checkRole)) return 0;
        $res += $pengajuan->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_PERLU_REVISI, "tgl_arahan_revisi", now());
        $res += $pengajuan->riwayatPengajuan->update(["catatan_butuh_perbaikan" => $data["catatan_butuh_perbaikan"]]);
        return $res != 0;
    }

    public static function ajukanRevisi($data)
    {
        $res = 0;
        $pengajuan = self::with("riwayatPengajuan")->find($data['id']);
        if (!$pengajuan->canAjukanRevisi()) return 0;
        $res += $pengajuan->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIKIRIM, "tgl_dikirim", now());
        $res += $pengajuan->update($data);
        return $res != 0;
    }

    public function canSetujui(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
                auth()->user()->hasRole("kepala_satker") ||
                $this->plh_id == auth()->user()->pegawai?->nip
            );
    }
    public function canRevisi(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM;
    }
    public function canAjukanRevisi(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI;
    }
    public function canTolak(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
                $this->plh_id == auth()->user()->pegawai?->nip ||
                auth()->user()->hasRole("kepala_satker")
            );
    }
    public function canBatalkan(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return (
                $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM ||
                $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI ||
                $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DISETUJUI ||
                $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DICETAK
            ) &&
            (
                auth()->user()->pegawai?->nip == $this->nip ||
                auth()->user()->pegawai?->nip == $this->nip_pengaju ||
                auth()->user()->pegawai?->nip == $this->kegiatan->pj_kegiatan_id ||
                auth()->user()->hasRole("operator_umum")
            );
    }
    public function canCetak(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DISETUJUI &&
            (
                auth()->user()->pegawai->nip == $this->nip ||
                auth()->user()->hasRole('operator_umum')
            );
    }
    public function canKumpulkan(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DICETAK &&
            $this->jenis_surat_tugas != Constants::NON_SPPD &&
            (
                auth()->user()->hasRole('operator_umum')
            );
    }
    public function canBatalkanPengumpulan(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKUMPULKAN &&
            $this->jenis_surat_tugas != Constants::NON_SPPD &&
            (
                auth()->user()->hasRole('operator_umum')
            );
    }
    public function canCairkan(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return
            $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKUMPULKAN &&
            $this->jenis_surat_tugas != Constants::NON_SPPD &&
            (
                auth()->user()->hasRole('operator_umum')
            );
    }
    public function canPerluPerbaikan(bool $checkRole = true)
    {
        if ($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM &&
            (
                $this->plh_id == auth()->user()->pegawai?->nip ||
                auth()->user()->hasRole("kepala_satker")
            );
    }

    public function assignNomorSuratTugas($mode_langsung = false, $data = null)
    {
        $p = Penugasan::where('grup_id', $this->grup_id)
            ->whereNotNull('surat_tugas_id')
            ->first();
        if ($p) {
            $this->surat_tugas_id = $p->surat_tugas_id;
            return $this->save();
        }
        if ($mode_langsung) {
            $surat_tugas = NomorSurat::create([
                "nomor" => $data["nomor"],
                "sub_nomor" => $data["subnomor"] == '' ? null : $data["subnomor"],
                "tanggal_nomor" => $data["tanggal_nomor"],
                "jenis" => Constants::JENIS_NOMOR_SURAT_TUGAS,
                "tahun" => $data["tanggal_nomor"]->year
            ]);
            $this->surat_tugas_id = $surat_tugas->id;
            return $this->save();
        }
        $this->surat_tugas_id = NomorSurat::generateNomorSuratTugas(Carbon::parse($this->tgl_pengajuan_tugas))->id;
        return $this->save();
    }
    public function assignNomorSuratPerjadin()
    {
        $p = Penugasan::where('grup_id', $this->grup_id)
            ->whereNotNull('surat_perjadin_id')
            ->first();
        if ($p) {
            $this->surat_perjadin_id = $p->surat_perjadin_id;
            return $this->save();
        }
        $this->surat_perjadin_id = NomorSurat::generateNomorSuratPerjadin(Carbon::parse($this->tgl_pengajuan_tugas))->id;
        return $this->save();
    }

    public function setujui(bool $checkRole = true, $mode_langsung = false, $data = null)
    {
        if (!$this->canSetujui($checkRole)) return 0;
        if (!$this->surat_tugas_id) $this->assignNomorSuratTugas($mode_langsung, $data);
        if ($this->jenis_surat_tugas != Constants::NON_SPPD) $this->assignNomorSuratPerjadin();
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DISETUJUI, "tgl_diterima", now());
    }
    public function tolak(bool $checkRole = true)
    {
        if (!$this->canTolak($checkRole)) return 0;
        $suratTugas = $this->suratTugas;
        $suratPerjadin = $this->suratPerjadin;
        $suratTugas->delete();
        $suratPerjadin->delete();
        $this->save();
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DITOLAK, "tgl_ditolak", now());
    }
    public function batalkan(bool $checkRole = true)
    {
        if (!$this->canBatalkan($checkRole)) return 0;
        $suratTugas = $this->suratTugas;
        $suratPerjadin = $this->suratPerjadin;
        $this->surat_tugas_id = null;
        $this->surat_perjadin_id = null;
        $this->save();
        $suratTugas?->delete();
        $suratPerjadin?->delete();
        $this->delete();

        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIBATALKAN, "tgl_dibatalkan", now());
    }
    public function cetak(bool $checkRole = true)
    {
        if (!$this->canCetak($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICETAK, "tgl_dibuat", now());
    }
    public function kumpulkan(bool $checkRole = true)
    {
        if (!$this->canKumpulkan($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIKUMPULKAN, "tgl_dikumpulkan", now());
    }
    public function batalkanPengumpulan(bool $checkRole = true)
    {
        if (!$this->canBatalkanPengumpulan($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICETAK, "tgl_dibuat", now());
    }
    public function cairkan(bool $checkRole = true)
    {
        if (!$this->canCairkan($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICAIRKAN, "tgl_pencairan", now());
    }
    public static function sedangPerjadin($nip, $date)
    {
        $res = self::whereNotIn('jenis_surat_tugas', [
            Constants::NON_SPPD
        ])
            ->where('nip', $nip)
            ->whereDate('tgl_mulai_tugas', '<=', $date)
            ->whereDate('tgl_akhir_tugas', '>=', $date)
            ->whereHas('riwayatPengajuan', function ($q) {
                $q->whereIn('status', [
                    Constants::STATUS_PENGAJUAN_DISETUJUI,
                    Constants::STATUS_PENGAJUAN_DICETAK,
                    Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                    Constants::STATUS_PENGAJUAN_DICAIRKAN,
                ]);
            })->first();
        return $res;
    }



    public static function getGrupId(): string
    {
        $id = Str::orderedUuid();
        return $id;
    }
    public static function getDisabledDates(array $nips): array
    {
        $penugasans = Penugasan::whereIn("nip", $nips)
            ->whereNotNull("surat_perjadin_id")
            ->whereHas('riwayatPengajuan', function (Builder $query) {
                $query->whereIn(
                    'status',
                    [
                        Constants::STATUS_PENGAJUAN_DIKIRIM,
                        Constants::STATUS_PENGAJUAN_DISETUJUI,
                        Constants::STATUS_PENGAJUAN_DICETAK,
                        Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                        Constants::STATUS_PENGAJUAN_DICAIRKAN,
                        Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                    ]
                )
                    ->whereDate('tgl_mulai_tugas', ">=", now()->subMonth(2))
                    ->whereDate('tgl_akhir_tugas', "<=", now()->addMonth(2))
                ;
            })->get();
        $res = [];
        foreach ($penugasans as $p) {
            $res = array_merge($res, self::generateDateRange(Carbon::parse($p->tgl_mulai_tugas), Carbon::parse($p->tgl_akhir_tugas)));
        }
        return collect($res)->unique()->flatten()->toArray();
    }
    public static function generateDateRange(Carbon $start_date, Carbon $end_date)
    {
        $dates = [];

        for ($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
    public static function getMinDate(string $date, array $nips)
    {
        $date = Carbon::parse($date);
        // dd( collect(self::getDisabledDates($nips)));
        $res = collect(self::getDisabledDates($nips))->filter(fn($v) => Carbon::parse($v) > $date)->sort()->flatten()->toArray();
        return $res ? $res[0] : null;
    }

    public static function getNearestPemberiTugasDate(string $tanggalPengajuan, string $tanggalMulaiTugas, array $nips)
    {
        $dateRange = self::generateDateRange(Carbon::parse($tanggalPengajuan), Carbon::parse($tanggalMulaiTugas));
        $disabledDate = self::getDisabledDates($nips);
        $tanggalMerah = TanggalMerah::getLiburDates();
        return collect(array_diff($dateRange, $disabledDate, $tanggalMerah))->unique()->sort()->flatten()->toArray()[0] ?? $tanggalMulaiTugas;
    }
    public static function dataToArray($data, $key)
    {
        if (!isset($data[$key])) return null;
        if (!is_array($data[$key])) return [$data[$key]];
        return $data[$key];
    }

    public static function importSuratTugas($data)
    {
        $now = $data['tanggal_mulai'];
        if ($data["jenis_petugas"] == "MITRA") {
            $data["mitras"] = [$data["nip"]];
        } else {
            $data["nips"] = [$data["nip"]];
        }
        $grupId = $data['grup_id'];
        $res = 0;
        $pegawaiPlh = Plh::getApprover($data["nips"] ?? null, Carbon::parse($data['tanggal_mulai'])->toDateTimeString(), true);
        $data["mitras"] = $data["mitras"] ?? [];
        $data["nips"] = $data["nips"] ?? [];

        $data["nama_tempat_tujuan"] = $data["lokasi"];
        $data["prov_ids"] = ["61"];

        $lokasi = [];
        if ($data['level_tujuan'] == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA) {
            $lokasi = MasterSls::getIdByName($data["kabupaten"], $data["level_tujuan"]);
        }
        if ($data['level_tujuan'] == Constants::LEVEL_PENUGASAN_KECAMATAN) {
            $lokasi = MasterSls::getIdByName($data["kecamatan"], $data["level_tujuan"], $data);
        }
        if ($data['level_tujuan'] == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN) {
            $lokasi = MasterSls::getIdByName($data["desa"], $data["level_tujuan"]);
        }

        $data["prov_ids"] = $lokasi["prov_ids"] ?? null;
        $data["kabkot_ids"] = $lokasi["kabkot_ids"] ?? null;
        $data["kecamatan_ids"] = $lokasi["kecamatan_ids"] ?? null;
        $data["desa_kel_ids"] = $lokasi["desa_kel_ids"] ?? null;

        $data["prov_ids"] = self::dataToArray($data, "prov_ids");
        $data["kabkot_ids"] = self::dataToArray($data, "kabkot_ids");
        $data["kecamatan_ids"] = self::dataToArray($data, "kecamatan_ids");
        $data["desa_kel_ids"] = self::dataToArray($data, "desa_kel_ids");


        $data["kegiatan_id"] = Kegiatan::where("nama", trim($data["nama_kegiatan"]))?->first()?->id;
        if ($data["kegiatan_id"] == null) dd($data["nama_kegiatan"]);
        $data["level_tujuan_penugasan"] = $data["level_tujuan"];
        $data["tbh_hari_jalan_awal"] = null;
        $data["tbh_hari_jalan_akhir"] = null;
        $data["tgl_mulai_tugas"] = $data['tanggal_mulai'];
        $data["tgl_akhir_tugas"] = $data["tanggal_selesai"];
        $data["tgl_pengajuan_tugas"] = Carbon::parse($data['tanggal_mulai'])->addDay(-1);
        $data["jenis_peserta"] = ($data["jenis_petugas"] == "MITRA") ? Constants::JENIS_PESERTA_SURAT_TUGAS_MITRA : Constants::JENIS_PESERTA_SURAT_TUGAS_PEGAWAI;
        $data["jenis_surat_tugas"] = (1 * $data['apakah_sppd'] == 1) ? Constants::PERJALAN_DINAS_DALAM_KOTA : Constants::NON_SPPD;
        $data["transportasi"] = Constants::TRANSPORTASI_KENDARAAN_PRIBADI;
        $pengajuan = null;

        foreach ($data["nips"] as $n) {
            $pengajuan = self::create([
                "nip" => $n,
                "kegiatan_id" => $data["kegiatan_id"],
                "nip_pengaju" => $n,
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "tgl_mulai_tugas" => Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),
                "tgl_akhir_tugas" => Carbon::parse($data["tgl_akhir_tugas"])->toDateTimeString(),
                "tbh_hari_jalan_awal" => $data["tbh_hari_jalan_awal"] ?? null,
                "tbh_hari_jalan_akhir" => $data["tbh_hari_jalan_akhir"] ?? null,
                "tgl_pengajuan_tugas" => $data["tgl_pengajuan_tugas"] ?? self::getNearestPemberiTugasDate(now() >= Carbon::parse($data["tgl_mulai_tugas"]) ? Carbon::parse($data["tgl_mulai_tugas"])->toDateString() : now(), Carbon::parse($data["tgl_mulai_tugas"])->toDateString(), $data["nips"]),
                "jenis_peserta" => $data["jenis_peserta"] ?? null,
                "grup_id" => $grupId,
                "jenis_surat_tugas" => $data["jenis_surat_tugas"],
                "plh_id" => $pegawaiPlh->nip,
                "transportasi" => $data["transportasi"] ?? null,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            TujuanSuratTugas::ajukan($data, $pengajuan->id);
            if ($pengajuan) $res += 1;
        }
        foreach ($data["mitras"] as $n) {
            $pengajuan = self::create([
                "id_sobat" => $n,
                "kegiatan_id" => $data["kegiatan_id"],
                "nip_pengaju" => $n,
                "level_tujuan_penugasan" => $data["level_tujuan_penugasan"],
                "tgl_mulai_tugas" => Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),
                "tgl_akhir_tugas" => Carbon::parse($data["tgl_akhir_tugas"])->toDateTimeString(),
                "tbh_hari_jalan_awal" => $data["tbh_hari_jalan_awal"] ?? null,
                "tbh_hari_jalan_akhir" => $data["tbh_hari_jalan_akhir"] ?? null,
                "tgl_pengajuan_tugas" => $data["tgl_pengajuan_tugas"] ?? self::getNearestPemberiTugasDate(now() >= Carbon::parse($data["tgl_mulai_tugas"]) ? Carbon::parse($data["tgl_mulai_tugas"])->toDateString() : now(), Carbon::parse($data["tgl_mulai_tugas"])->toDateString(), $data["nips"]),
                "jenis_peserta" => $data["jenis_peserta"] ?? null,
                "grup_id" => $grupId,
                "jenis_surat_tugas" => $data["jenis_surat_tugas"],
                "plh_id" => $pegawaiPlh->nip,
                "transportasi" => $data["transportasi"] ?? null,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            TujuanSuratTugas::ajukan($data, $pengajuan->id);
            if ($pengajuan) $res += 1;
        }
        if ($pengajuan == null) dump($data);
        if ($pengajuan?->jenis_surat_tugas == Constants::NON_SPPD || Carbon::parse($pengajuan->tgl_akhir_tugas)->lessThan(now())) $pengajuan?->setujui(checkRole: false, mode_langsung: true, data: $data);
        if (
            $pengajuan?->jenis_surat_tugas != Constants::NON_SPPD &&
            Carbon::parse($pengajuan->tgl_akhir_tugas)->lessThan(now()->addDays(-2))
        ) {
            // dump("start");
            $pengajuan?->cetak(checkRole: false);
            // dump("end");
        }
        if (
            $pengajuan?->jenis_surat_tugas != Constants::NON_SPPD &&
            Carbon::parse($pengajuan->tgl_akhir_tugas)->lessThan(now()->addDays(-7))
        ) {
            // dump("start");
            $pengajuan?->kumpulkan(checkRole: false);
            // dump("end");
        }
        if (
            $pengajuan?->jenis_surat_tugas != Constants::NON_SPPD &&
            Carbon::parse($pengajuan->tgl_akhir_tugas)->lessThan(now()->addMonths(-1))
        ) {
            // dump("start");
            $pengajuan?->cairkan(checkRole: false);
            // dump("end");
        }

        return null;
    }
}
