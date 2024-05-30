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
    protected $guarded=[];
    public static Collection $masterSls;

    protected function casts(): array
    {
        return [
            'plh_id' => 'string',
        ];
    }

    protected function pemberiPerintah(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Plh::getApprover([$attributes['nip']],$attributes['tgl_pengajuan_tugas'],true),
        );
    }
    protected function penandaTanganHariPertama(): Attribute
    {
        return $this->pemberiPerintah();
    }

    protected function jenisSurat(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Constants::JENIS_SURAT_TUGAS_OPTIONS[$attributes["jenis_surat_tugas"]],
        );
    }
    protected function jenisTransportasi(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Constants::JENIS_TRANSPORTASI_OPTIONS[$attributes["transportasi"]],
        );
    }
    protected function lamaPerjadin(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes){
                return ((int) Carbon::parse($attributes['tgl_mulai_tugas'])->diffInDays(Carbon::parse($attributes['tgl_akhir_tugas'])))+1;
            },
        );
    }
    protected function jenisPerjadin(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes){
                if(!$attributes['jenis_surat_tugas']) return null;
                return Constants::JENIS_SURAT_TUGAS_OPTIONS[$attributes['jenis_surat_tugas']];
            },
        );
    }
    protected function tujuanPenugasan(): Attribute
    {
        $masterSls = self::$masterSls ??= MasterSls::get();
        return Attribute::make(get: function(mixed $value, array $attributes) use($masterSls){
            if($attributes['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_KABUPATEN_KOTA) return ucwords(strtolower($masterSls->where('kabkot_id',$attributes['kabkot_id'])->first()->kabkot));
            if($attributes['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_KECAMATAN) return ucwords(strtolower($masterSls->where('kec_id',$attributes['kecamatan_id'])->first()->kecamatan));
            if($attributes['level_tujuan_penugasan'] == Constants::LEVEL_PENUGASAN_DESA_KELURAHAN) return ucwords(strtolower($masterSls->where('desa_kel_id',$attributes['desa_kel_id'])->first()->desa_kel));
            return "";
        });
    }
    protected function tglPerjadin(): Attribute
    {

        return Attribute::make(
            get: function (mixed $value, array $attributes){
                if(Carbon::parse($attributes['tgl_mulai_tugas'])->toDateString() == Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString())
                        return Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString();
                        return Carbon::parse($attributes['tgl_mulai_tugas'])->toDateString()." - ".Carbon::parse($attributes['tgl_akhir_tugas'])->toDateString();
            },
        );
    }

    public function suratTugas(){
        return $this->belongsTo(NomorSurat::class,"surat_tugas_id","id");
    }
    public function suratPerjadin(){
        return $this->belongsTo(NomorSurat::class,"surat_perjadin_id","id");
    }

    public function pegawai(){
        return $this->belongsTo(Pegawai::class,"nip","nip");
    }
    public function desa(){
        return $this->belongsTo(MasterSls::class,"desa_kel_id","desa_kel_id");
    }
    public function kecamatan(){
        return $this->belongsTo(MasterSls::class,"kecamatan_id","kec_id");
    }
    public function kabkot(){
        return $this->belongsTo(MasterSls::class,"kabkot_id","kabkot_id");
    }
    public function provinsi(){
        return $this->belongsTo(MasterSls::class,"prov_id");
    }
    public function plh(){
        return $this->belongsTo(Pegawai::class,"plh_id","nip");
    }
    public function riwayatPengajuan(){
        return $this->hasOne(RiwayatPengajuan::class,"penugasan_id","id");
    }
    public function kegiatan(){
        return $this->belongsTo(Kegiatan::class,"kegiatan_id","id");
    }
    public function satuSurat(){
        return $this->hasMany(Penugasan::class,"surat_tugas_id","surat_tugas_id");
    }
    public function suratTugasBersamaDisetujui(array $with = null){
        $query = self::query();
        $surat_tugas_id = $this->surat_tugas_id;
        if(!$surat_tugas_id) return collect([]);
        if($with) $query = $query->with($with);
        return $query->where("surat_tugas_id",$surat_tugas_id)->whereHas('riwayatPengajuan',function($q){
            return $q->where(function($q){
                $q->where('status',Constants::STATUS_PENGAJUAN_DISETUJUI)
                ->orWhere('status',Constants::STATUS_PENGAJUAN_DICETAK)
                ->orWhere('status',Constants::STATUS_PENGAJUAN_DIKUMPULKAN)
                ->orWhere('status',Constants::STATUS_PENGAJUAN_DICAIRKAN);
            })
            ;
        })->get();
    }
    public function satuGrupPengajuan(){
        return $this->hasMany(Penugasan::class,"grup_id","grup_id");
    }
    public static function ajukan(array $data){
        $now = now()->toDateTimeString();
        $res = 0;
        $pegawaiPlh = Plh::getApprover($data["nips"],Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),true);
        $grupId = self::getGrupId();
        foreach($data["nips"] as $n){
            $pengajuan = self::create([
                "nip"=>$n,
                "kegiatan_id"=>$data["kegiatan_id"],
                "level_tujuan_penugasan"=>$data["level_tujuan_penugasan"],
                "nama_tempat_tujuan"=>$data["nama_tempat_tujuan"] ?? null,
                "tgl_mulai_tugas"=>Carbon::parse($data["tgl_mulai_tugas"])->toDateTimeString(),
                "tgl_akhir_tugas"=>Carbon::parse($data["tgl_akhir_tugas"])->toDateTimeString(),
                "tbh_hari_jalan_awal"=>$data["tbh_hari_jalan_awal"] ?? null,
                "tbh_hari_jalan_akhir"=>$data["tbh_hari_jalan_akhir"] ?? null,
                "tgl_pengajuan_tugas"=>$data["tgl_pengajuan_tugas"] ?? self::getNearestPemberiTugasDate(now() >= Carbon::parse($data["tgl_mulai_tugas"]) ?Carbon::parse($data["tgl_mulai_tugas"])->toDateString() : now() ,Carbon::parse($data["tgl_mulai_tugas"])->toDateString(),$data["nips"]),
                "prov_id"=>$data["prov_id"] ?? null,
                "kabkot_id"=>$data["kabkot_id"] ?? null,
                "kecamatan_id"=>$data["kecamatan_id"] ?? null,
                "desa_kel_id"=>$data["desa_kel_id"] ?? null,
                "grup_id"=>$grupId,
                "jenis_surat_tugas" => $data["jenis_surat_tugas"],
                "plh_id"=>$pegawaiPlh->nip,
                "transportasi"=>$data["transportasi"] ?? null,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            if($pengajuan) $res+=1;
        }
        if($res == count($data["nips"])) return true;
        return null;

    }

    public static function perluPerbaikan($data,bool $checkRole = false){
        $res = 0;
        $pengajuan = self::with("riwayatPengajuan")->find($data['id']);
        if(!$pengajuan->canPerluPerbaikan($checkRole)) return 0;
        $res += $pengajuan->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_PERLU_REVISI,"tgl_arahan_revisi",now());
        $res += $pengajuan->riwayatPengajuan->update(["catatan_butuh_perbaikan"=>$data["catatan_butuh_perbaikan"]]);
        return $res!=0;
    }
    public static function ajukanRevisi($data){
        $res = 0;
        $pengajuan = self::with("riwayatPengajuan")->find($data['id']);
        if(!$pengajuan->canAjukanRevisi()) return 0;
        $res += $pengajuan->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIKIRIM,"tgl_dikirim",now());
        $res += $pengajuan->update($data);
        return $res!=0;
    }

    public function canSetujui(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
            auth()->user()->hasRole("kepala_satker") ||
            $this->plh_id == auth()->user()->pegawai?->nip
        )

        ;
    }
    public function canRevisi(bool $checkRole = true){
        if($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM;
    }
    public function canAjukanRevisi(bool $checkRole = true){
        if($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI;
    }
    public function canTolak(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
            $this->plh_id == auth()->user()->pegawai?->nip ||
            auth()->user()->hasRole("kepala_satker")
        )
        ;
    }
    public function canBatalkan(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        ($this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM ||
         $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI) &&
         auth()->user()->pegawai?->nip == $this->nip
         ;
    }
    public function canCetak(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DISETUJUI &&
        (
            auth()->user()->pegawai->nip == $this->nip ||
            auth()->user()->hasRole('operator_umum')
        )

        ;
    }
    public function canKumpulkan(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DICETAK &&
        $this->jenis_surat_tugas != Constants::NON_SPPD &&
        (
            auth()->user()->hasRole('operator_umum')
        )
        ;
    }
    public function canCairkan(bool $checkRole = true){
        if($checkRole == false) return true;
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKUMPULKAN &&
        $this->jenis_surat_tugas != Constants::NON_SPPD &&
        (
            auth()->user()->hasRole('operator_umum')
        )
        ;
    }
    public function canPerluPerbaikan(bool $checkRole = true){
        if($checkRole == false) return true;
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM &&
        (
            $this->plh_id == auth()->user()->pegawai?->nip ||
            auth()->user()->hasRole("kepala_satker")
        );
    }

    public function assignNomorSuratTugas(){
        $p = Penugasan::where('grup_id',$this->grup_id)
                        ->whereNotNull('surat_tugas_id')
                        ->first();
        if($p){
            $this->surat_tugas_id = $p->surat_tugas_id;
            return $this->save();
        }
        $this->surat_tugas_id = NomorSurat::generateNomorSuratTugas(Carbon::parse($this->tgl_pengajuan_tugas))->id;
        return $this->save();

    }
    public function assignNomorSuratPerjadin(){
        $p = Penugasan::where('grup_id',$this->grup_id)
                        ->whereNotNull('surat_perjadin_id')
                        ->first();
        if($p){
            $this->surat_perjadin_id = $p->surat_perjadin_id;
            return $this->save();
        }
        $this->surat_perjadin_id = NomorSurat::generateNomorSuratPerjadin(Carbon::parse($this->tgl_pengajuan_tugas))->id;
        return $this->save();

    }

    public function setujui(bool $checkRole = true){
        if(!$this->canSetujui($checkRole)) return 0;
        if(!$this->surat_tugas_id) $this->assignNomorSuratTugas();
        if($this->jenis_surat_tugas != Constants::NON_SPPD) $this->assignNomorSuratPerjadin();
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DISETUJUI,"tgl_diterima",now());
    }
    public function tolak(bool $checkRole = true){
        if(!$this->canTolak($checkRole)) return 0;
        $suratTugas = $this->suratTugas;
        $suratPerjadin = $this->suratPerjadin;
        $this->surat_tugas_id = null;
        $this->surat_perjadin_id = null;
        $this->save();
        $suratTugas->delete();
        $suratPerjadin->delete();
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DITOLAK,"tgl_ditolak",now());
    }
    public function batalkan(bool $checkRole = true){
        if(!$this->canBatalkan($checkRole)) return 0;
        $suratTugas = $this->suratTugas;
        $suratPerjadin = $this->suratPerjadin;
        $this->surat_tugas_id = null;
        $this->surat_perjadin_id = null;
        $this->save();
        $suratTugas->delete();
        $suratPerjadin->delete();
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIBATALKAN,"tgl_dibatalkan",now());
    }
    public function cetak(bool $checkRole = true){
        if(!$this->canCetak($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICETAK,"tgl_dibuat",now());
    }
    public function kumpulkan(bool $checkRole = true){
        if(!$this->canKumpulkan($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIKUMPULKAN,"tgl_dikumpulkan",now());
    }
    public function cairkan(bool $checkRole = true){
        if(!$this->canCairkan($checkRole)) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICAIRKAN,"tgl_pencairan",now());
    }

    public static function getGrupId(): string {
        $id = Str::orderedUuid();
        return $id;
    }
    public static function getDisabledDates(array $nips): array{
        $penugasans = Penugasan::whereIn("nip",$nips)
                        ->whereHas('riwayatPengajuan',function (Builder $query){
                            $query->whereIn('status',
                            [
                                Constants::STATUS_PENGAJUAN_DIKIRIM,
                                Constants::STATUS_PENGAJUAN_DISETUJUI,
                                Constants::STATUS_PENGAJUAN_DICETAK,
                                Constants::STATUS_PENGAJUAN_DIKUMPULKAN,
                                Constants::STATUS_PENGAJUAN_DICAIRKAN,
                                Constants::STATUS_PENGAJUAN_PERLU_REVISI,
                            ])
                            ->whereDate('tgl_mulai_tugas',">=",now()->subMonth(2))
                            ->whereDate('tgl_akhir_tugas',"<=",now()->addMonth(2))
                            ;
                        })->get();
        $res = [];
        foreach ($penugasans as $p) {
            $res=array_merge($res,self::generateDateRange(Carbon::parse($p->tgl_mulai_tugas),Carbon::parse($p->tgl_akhir_tugas)));
        }
        return collect($res)->unique()->flatten()->toArray();
    }
    public static function generateDateRange(Carbon $start_date, Carbon $end_date)
    {
        $dates = [];

        for($date = $start_date->copy(); $date->lte($end_date); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
    public static function getMinDate(string $date,array $nips){
        $date = Carbon::parse($date);
        // dd( collect(self::getDisabledDates($nips)));
        $res = collect(self::getDisabledDates($nips))->filter(fn($v)=>Carbon::parse($v)>$date)->sort()->flatten()->toArray();
        return $res ? $res[0] : null;
    }

    public static function getNearestPemberiTugasDate(string $tanggalPengajuan,string $tanggalMulaiTugas,array $nips){
        $dateRange = self::generateDateRange(Carbon::parse($tanggalPengajuan),Carbon::parse($tanggalMulaiTugas));
        $disabledDate = self::getDisabledDates($nips);
        $tanggalMerah = TanggalMerah::getLiburDates();
        return collect(array_diff($dateRange,$disabledDate,$tanggalMerah))->unique()->sort()->flatten()->toArray()[0] ?? $tanggalMulaiTugas;
    }

}
