<?php

namespace App\Models;

use App\DTO\PenugasanCreation;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Casts\Attribute;
use PHPUnit\TextUI\Configuration\Constant;

class Penugasan extends Model
{
    use HasFactory;
    protected $guarded=[];

    protected function casts(): array
    {
        return [
            'plh_id' => 'string',
        ];
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
    public static function ajukan(PenugasanCreation $penugasanDTO){
        $now = now()->toDateTimeString();
        $res = 0;
        $orderedUuid = (string) Str::orderedUuid();
        $pegawaiPlh = Plh::getApprover($penugasanDTO->nips,Carbon::parse($penugasanDTO->tglMulaiTugas)->toDateTimeString(),true);
        foreach($penugasanDTO->nips as $n){
            $pengajuan = self::create([
                "nip"=>$n,
                "kegiatan_id"=>$penugasanDTO->kegiatanId,
                "tgl_mulai_tugas"=>Carbon::parse($penugasanDTO->tglMulaiTugas)->toDateTimeString(),
                "tgl_akhir_tugas"=>Carbon::parse($penugasanDTO->tglAkhirTugas)->toDateTimeString(),
                "tbh_hari_jalan_awal"=>$penugasanDTO->tbhHariJalanAwal,
                "tbh_hari_jalan_akhir"=>$penugasanDTO->tbhHariJalanAkhir,
                "prov_id"=>$penugasanDTO->provId,
                "kabkot_id"=>$penugasanDTO->kabkotId,
                "kecamatan_id"=>$penugasanDTO->kecamatanId,
                "desa_kel_id"=>$penugasanDTO->desaKelId,
                "surat_tugas_id" => $orderedUuid,
                "jenis_surat_tugas" => $penugasanDTO->jenisSuratTugas,
                "plh_id"=>$pegawaiPlh->nip,
                "transportasi"=>$penugasanDTO->transportasi,
            ]);
            RiwayatPengajuan::kirim([$pengajuan->id]);
            if($pengajuan) $res+=1;
        }
        if($res == count($penugasanDTO->nips)) return true;
        return null;

    }

    public static function perluPerbaikan($data){
        $res = 0;
        $pengajuan = self::with("riwayatPengajuan")->find($data['id']);
        if(!$pengajuan->canPerluPerbaikan()) return 0;
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

    public function canSetujui(){
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
            auth()->user()->hasRole("kepala_satker") ||
            $this->plh_id == auth()->user()->pegawai?->nip
        )

        ;
    }
    public function canRevisi(){
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM;
    }
    public function canAjukanRevisi(){
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI;
    }
    public function canTolak(){
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM && (
            $this->plh_id == auth()->user()->pegawai?->nip ||
            auth()->user()->hasRole("kepala_satker")
        )
        ;
    }
    public function canBatalkan(){
        return
        ($this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM ||
         $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_PERLU_REVISI) &&
         auth()->user()->pegawai?->nip == $this->nip
         ;
    }
    public function canCetak(){
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DISETUJUI &&
        (
            auth()->user()->pegawai->nip == $this->nip ||
            auth()->user()->hasRole('operator_umum')
        )

        ;
    }
    public function canKumpulkan(){
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DICETAK &&
        (
            auth()->user()->hasRole('operator_umum')
        )
        ;
    }
    public function canCairkan(){
        return
        $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKUMPULKAN &&
        (
            auth()->user()->hasRole('operator_umum')
        )
        ;
    }
    public function canPerluPerbaikan(){
        return $this->riwayatPengajuan->status == Constants::STATUS_PENGAJUAN_DIKIRIM &&
        (
            $this->plh_id == auth()->user()->pegawai?->nip ||
            auth()->user()->hasRole("kepala_satker")
        );
    }

    public function setujui(){
        if(!$this->canSetujui()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DISETUJUI,"tgl_diterima",now());
    }
    public function tolak(){
        if(!$this->canTolak()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DITOLAK,"tgl_ditolak",now());
    }
    public function batalkan(){
        if(!$this->canBatalkan()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIBATALKAN,"tgl_dibatalkan",now());
    }
    public function cetak(){
        if(!$this->canCetak()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICETAK,"tgl_dibuat",now());
    }
    public function kumpulkan(){
        if(!$this->canKumpulkan()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DIKUMPULKAN,"tgl_dikumpulkan",now());
    }
    public function cairkan(){
        if(!$this->canCairkan()) return 0;
        return $this->riwayatPengajuan->updateStatus(Constants::STATUS_PENGAJUAN_DICAIRKAN,"tgl_pencairan",now());
    }

}
