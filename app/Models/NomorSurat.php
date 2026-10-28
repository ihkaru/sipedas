<?php

namespace App\Models;

use App\Supports\Constants;
use App\Supports\TanggalMerah;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $nomor
 * @property int|null $sub_nomor
 * @property string|null $tanggal_nomor
 * @property string|null $jenis
 * @property int|null $tahun
 * @property-read string|null $nomor_surat_tugas
 * @property-read string|null $nomor_surat_perjadin
 */
class NomorSurat extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function penugasans()
    {
        return $this->hasMany(Penugasan::class, "surat_tugas_id");
    }

    public static function generateNomorSuratTugas(Carbon $tanggal_pengajuan)
    {
        $nomorSuratTerakhir = self::where("tahun", $tanggal_pengajuan->year)
            ->where('jenis', Constants::JENIS_NOMOR_SURAT_TUGAS)
            ->orderBy("tanggal_nomor", "desc")
            ->orderBy('nomor', 'desc')
            ->first();
        if (!$nomorSuratTerakhir) {
            // dump("lok1");
            $nomorSuratTerakhir = self::create([
                "nomor" => 1,
                "tanggal_nomor" => $tanggal_pengajuan,
                "jenis" => Constants::JENIS_NOMOR_SURAT_TUGAS,
                "tahun" => $tanggal_pengajuan->year
            ]);
            $nomorSuratBaru = $nomorSuratTerakhir;
        } else {
            $tanggal_nomor_terakhir = Carbon::parse($nomorSuratTerakhir->tanggal_nomor);
            if ($tanggal_nomor_terakhir->startOfDay() <= $tanggal_pengajuan->startOfDay()) {
                // dump("lok2");
                $nomorSuratBaru = self::create([
                    'nomor' => $nomorSuratTerakhir->nomor + 1,
                    'tanggal_nomor' => $tanggal_pengajuan,
                    'jenis' => Constants::JENIS_NOMOR_SURAT_TUGAS,
                    "tahun" => $tanggal_pengajuan->year
                ]);
            }
            if ($tanggal_nomor_terakhir->startOfDay() > $tanggal_pengajuan->startOfDay()) {
                // dump($tanggal_nomor_terakhir,$tanggal_pengajuan);
                $nomor_terakhir_sesuai_tanggal_pengajuan = self::whereDate("tanggal_nomor", "<=", $tanggal_pengajuan->toDateString())
                    ->where("tahun", $tanggal_pengajuan->year)
                    ->where('jenis', Constants::JENIS_NOMOR_SURAT_TUGAS)
                    ->orderBy('tanggal_nomor', 'desc')
                    ->orderBy('nomor', 'asc')
                    ->orderBy('sub_nomor', 'desc')
                    ->first();
                if (!$nomor_terakhir_sesuai_tanggal_pengajuan) {
                    // dump("lok3");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::orderBy('nomor', 'asc')
                        ->where("tahun", $tanggal_pengajuan->year)
                        ->where('jenis', Constants::JENIS_NOMOR_SURAT_TUGAS)
                        ->orderBy('sub_nomor', 'desc')
                        ->first();
                } else {
                    // dump("lok5");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::where('nomor', $nomor_terakhir_sesuai_tanggal_pengajuan->nomor)
                        ->where("tahun", $tanggal_pengajuan->year)
                        ->where('jenis', Constants::JENIS_NOMOR_SURAT_TUGAS)
                        ->orderBy('nomor', 'desc')
                        ->orderBy('sub_nomor', 'desc')
                        ->first();
                    // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                }
                // dump("lok4");
                // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                $nomorSuratBaru = self::create([
                    'nomor' => $nomor_terakhir_sesuai_tanggal_pengajuan->nomor,
                    'sub_nomor' => $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor ? $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor + 1 : 1,
                    'tanggal_nomor' => $tanggal_pengajuan,
                    "tahun" => $tanggal_pengajuan->year,
                    'jenis' => Constants::JENIS_NOMOR_SURAT_TUGAS,
                ]);
            }
        }
        // dump("Tanggal Pengajuan: $tanggal_pengajuan");
        // dump(NomorSurat::orderBy("tanggal_nomor","asc")->orderBy('nomor',"asc")->orderBy('sub_nomor')->get()->pluck('nomor_surat_tugas'));
        return $nomorSuratBaru;
    }
    public static function generateNomorSuratPerjadin(Carbon $tanggal_pengajuan)
    {
        $nomorSuratTerakhir = self::where("tahun", $tanggal_pengajuan->year)
            ->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
            ->orderBy("tanggal_nomor", "desc")
            ->orderBy('nomor', 'desc')
            ->first();
        if (!$nomorSuratTerakhir) {
            // dump("lok1");
            $nomorSuratTerakhir = self::create([
                "nomor" => 1,
                "tanggal_nomor" => $tanggal_pengajuan,
                "jenis" => Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                "tahun" => $tanggal_pengajuan->year
            ]);
            $nomorSuratBaru = $nomorSuratTerakhir;
        } else {
            $tanggal_nomor_terakhir = Carbon::parse($nomorSuratTerakhir->tanggal_nomor);
            if ($tanggal_nomor_terakhir->startOfDay() <= $tanggal_pengajuan->startOfDay()) {
                // dump("lok2");
                $nomorSuratBaru = self::create([
                    'nomor' => $nomorSuratTerakhir->nomor + 1,
                    'tanggal_nomor' => $tanggal_pengajuan,
                    'jenis' => Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                    "tahun" => $tanggal_pengajuan->year
                ]);
            }
            if ($tanggal_nomor_terakhir->startOfDay() > $tanggal_pengajuan->startOfDay()) {
                // dump($tanggal_nomor_terakhir,$tanggal_pengajuan);
                $nomor_terakhir_sesuai_tanggal_pengajuan = self::whereDate("tanggal_nomor", "<=", $tanggal_pengajuan->toDateString())
                    ->where("tahun", $tanggal_pengajuan->year)
                    ->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                    ->orderBy('tanggal_nomor', 'desc')
                    ->orderBy('nomor', 'asc')
                    ->orderBy('sub_nomor', 'desc')
                    ->first();
                if (!$nomor_terakhir_sesuai_tanggal_pengajuan) {
                    // dump("lok3");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::where("tahun", $tanggal_pengajuan->year)
                        ->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                        ->orderBy('nomor', 'asc')
                        ->orderBy('sub_nomor', 'desc')
                        ->first();
                } else {
                    // dump("lok5");
                    $nomor_terakhir_sesuai_tanggal_pengajuan = self::where('nomor', $nomor_terakhir_sesuai_tanggal_pengajuan->nomor)
                        ->where("tahun", $tanggal_pengajuan->year)
                        ->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS)
                        ->orderBy('nomor', 'desc')
                        ->orderBy('sub_nomor', 'desc')
                        ->first();
                    // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                }
                // dump("lok4");
                // dump($nomor_terakhir_sesuai_tanggal_pengajuan->nomorSuratTugas);
                $nomorSuratBaru = self::create([
                    'nomor' => $nomor_terakhir_sesuai_tanggal_pengajuan->nomor,
                    'sub_nomor' => $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor ? $nomor_terakhir_sesuai_tanggal_pengajuan->sub_nomor + 1 : 1,
                    'tanggal_nomor' => $tanggal_pengajuan,
                    "tahun" => $tanggal_pengajuan->year,
                    'jenis' => Constants::JENIS_NOMOR_SURAT_PERJALAN_DINAS,
                ]);
            }
        }
        // dump("Tanggal Pengajuan: $tanggal_pengajuan");
        // dump(NomorSurat::orderBy("tanggal_nomor","asc")->orderBy('nomor',"asc")->orderBy('sub_nomor')->get()->pluck('nomor_surat_tugas'));
        return $nomorSuratBaru;
    }

    public static function generateNomorSuratPerjanjianKerja(Carbon $tanggal_pengajuan)
    {
        $nomorSuratTerakhir = self::where("tahun", $tanggal_pengajuan->year)
            ->whereMonth('tanggal_nomor', $tanggal_pengajuan->month)
            ->where('jenis', Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA)
            ->orderBy('nomor', 'desc')
            ->first();

        if (!$nomorSuratTerakhir) {
            $nomorSuratBaru = self::create([
                "nomor" => 1,
                "tanggal_nomor" => $tanggal_pengajuan,
                "jenis" => Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
                "tahun" => $tanggal_pengajuan->year
            ]);
        } else {
            $nomorSuratBaru = self::create([
                'nomor' => $nomorSuratTerakhir->nomor + 1,
                'tanggal_nomor' => $tanggal_pengajuan,
                'jenis' => Constants::JENIS_NOMOR_SURAT_PERJANJIAN_KERJA,
                "tahun" => $tanggal_pengajuan->year
            ]);
        }

        return $nomorSuratBaru;
    }
    public function scopeBast(Builder $query): void
    {
        $query->where('jenis', Constants::JENIS_NOMOR_SURAT_BAST);
    }

    /**
     * Scope query untuk pencarian nomor surat berdasarkan keyword tanpa bergantung kolom virtual.
     */
    public function scopeSearchNomor(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nomor', 'like', "%{$search}%")
              ->orWhere('sub_nomor', 'like', "%{$search}%")
              ->orWhere('tahun', 'like', "%{$search}%");

            $cleanNumeric = preg_replace('/[^0-9]/', '', $search);
            if (!empty($cleanNumeric) && is_numeric($cleanNumeric)) {
                $q->orWhere('nomor', (int) $cleanNumeric);
            }
        });
    }

    public static function generateNomorSuratBast(Carbon $tanggal_pengajuan)
    {
        $nomorSuratTerakhir = self::where("tahun", $tanggal_pengajuan->year)
            ->whereMonth('tanggal_nomor', $tanggal_pengajuan->month)
            ->where('jenis', Constants::JENIS_NOMOR_SURAT_BAST)
            ->orderBy('nomor', 'desc')
            ->first();

        if (!$nomorSuratTerakhir) {
            $nomorSuratBaru = self::create([
                "nomor" => 1,
                "tanggal_nomor" => $tanggal_pengajuan,
                "jenis" => Constants::JENIS_NOMOR_SURAT_BAST,
                "tahun" => $tanggal_pengajuan->year
            ]);
        } else {
            $nomorSuratBaru = self::create([
                'nomor' => $nomorSuratTerakhir->nomor + 1,
                'tanggal_nomor' => $tanggal_pengajuan,
                'jenis' => Constants::JENIS_NOMOR_SURAT_BAST,
                "tahun" => $tanggal_pengajuan->year
            ]);
        }

        return $nomorSuratBaru;
    }

    protected function nomorSuratTugas(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $sub_nomor = isset($attributes['sub_nomor']) ? "." . $attributes['sub_nomor'] : "";
                $nomor = str_pad((string)($attributes['nomor'] ?? ''), 3, "0", STR_PAD_LEFT);
                $bulan = isset($attributes['bulan']) ? str_pad((string)$attributes['bulan'], 2, "0", STR_PAD_LEFT) : (isset($attributes['tanggal_nomor']) ? str_pad((string)Carbon::parse($attributes['tanggal_nomor'])->month, 2, "0", STR_PAD_LEFT) : now()->format('m'));
                $tahun = $attributes['tahun'] ?? (isset($attributes['tanggal_nomor']) ? Carbon::parse($attributes['tanggal_nomor'])->year : now()->year);

                $template = config('satker.format_surat.surat_tugas', 'B-{nomor}{sub_nomor}/{kode_surat}/{kode_klasifikasi}/{bulan}/{tahun}');
                return self::renderFormat($template, [
                    'nomor' => $nomor,
                    'sub_nomor' => $sub_nomor,
                    'kode_klasifikasi' => 'KP.650',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            },
        );
    }
    protected function nomorSuratPerjadin(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $sub_nomor = isset($attributes['sub_nomor']) ? "." . $attributes['sub_nomor'] : "";
                $nomor = str_pad((string)($attributes['nomor'] ?? ''), 3, "0", STR_PAD_LEFT);
                $bulan = isset($attributes['bulan']) ? str_pad((string)$attributes['bulan'], 2, "0", STR_PAD_LEFT) : (isset($attributes['tanggal_nomor']) ? str_pad((string)Carbon::parse($attributes['tanggal_nomor'])->month, 2, "0", STR_PAD_LEFT) : now()->format('m'));
                $tahun = $attributes['tahun'] ?? (isset($attributes['tanggal_nomor']) ? Carbon::parse($attributes['tanggal_nomor'])->year : now()->year);

                $template = config('satker.format_surat.sppd', '{nomor}{sub_nomor}/SPD/{bulan}/{tahun}');
                return self::renderFormat($template, [
                    'nomor' => $nomor,
                    'sub_nomor' => $sub_nomor,
                    'kode_klasifikasi' => 'PL.300',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            },
        );
    }
    protected function nomorSuratPerjanjianKerja(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $sub_nomor = isset($attributes['sub_nomor']) ? "." . $attributes['sub_nomor'] : "";
                $nomor = str_pad((string)($attributes['nomor'] ?? ''), 3, "0", STR_PAD_LEFT);
                $bulan = isset($attributes['bulan']) ? str_pad((string)$attributes['bulan'], 2, "0", STR_PAD_LEFT) : (isset($attributes['tanggal_nomor']) ? str_pad((string)Carbon::parse($attributes['tanggal_nomor'])->month, 2, "0", STR_PAD_LEFT) : now()->format('m'));
                $tahun = $attributes['tahun'] ?? (isset($attributes['tanggal_nomor']) ? Carbon::parse($attributes['tanggal_nomor'])->year : now()->year);

                $template = config('satker.format_surat.spk', '{nomor}{sub_nomor}/{kode_surat}/PL.300/{bulan}/{tahun}');
                return self::renderFormat($template, [
                    'nomor' => $nomor,
                    'sub_nomor' => $sub_nomor,
                    'kode_klasifikasi' => 'PL.300',
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            },
        );
    }
    protected function nomorSuratBast(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $sub_nomor = isset($attributes['sub_nomor']) ? "." . $attributes['sub_nomor'] : "";
                $nomor = str_pad((string)($attributes['nomor'] ?? ''), 3, "0", STR_PAD_LEFT);
                $bulan = isset($attributes['bulan']) ? str_pad((string)$attributes['bulan'], 2, "0", STR_PAD_LEFT) : (isset($attributes['tanggal_nomor']) ? str_pad((string)Carbon::parse($attributes['tanggal_nomor'])->month, 2, "0", STR_PAD_LEFT) : now()->format('m'));
                $tahun = $attributes['tahun'] ?? (isset($attributes['tanggal_nomor']) ? Carbon::parse($attributes['tanggal_nomor'])->year : now()->year);

                $template = config('satker.format_surat.bast', '{nomor}{sub_nomor}/{kode_satker}/BAST/{bulan}/{tahun}');
                return self::renderFormat($template, [
                    'nomor' => $nomor,
                    'sub_nomor' => $sub_nomor,
                    'bulan' => $bulan,
                    'tahun' => $tahun,
                ]);
            },
        );
    }

    public static function renderFormat(string $template, array $data): string
    {
        $bulanNum = (int) ($data['bulan'] ?? now()->month);

        $tokens = [
            '{nomor}'            => (string) ($data['nomor'] ?? ''),
            '{sub_nomor}'        => (string) ($data['sub_nomor'] ?? ''),
            '{kode_satker}'      => (string) config('satker.kode', '6104'),
            '{kode_surat}'       => (string) config('satker.kode_surat', config('satker.kode', '6104') . '1'),
            '{kode_klasifikasi}' => (string) ($data['kode_klasifikasi'] ?? 'KP.650'),
            '{bulan}'            => str_pad((string) $bulanNum, 2, '0', STR_PAD_LEFT),
            '{bulan_romawi}'     => self::toRomawi($bulanNum),
            '{tahun}'            => (string) ($data['tahun'] ?? now()->year),
        ];

        return str_replace(array_keys($tokens), array_values($tokens), $template);
    }

    public static function toRomawi(int $number): string
    {
        $map = [
            12 => 'XII', 11 => 'XI', 10 => 'X', 9 => 'IX', 8 => 'VIII',
            7 => 'VII', 6 => 'VI', 5 => 'V', 4 => 'IV', 3 => 'III', 2 => 'II', 1 => 'I'
        ];
        return $map[$number] ?? (string) $number;
    }

    public function alokasiHonor()
    {
        return $this->hasMany(AlokasiHonor::class, 'surat_perjanjian_kerja_id', 'id');
    }
}
