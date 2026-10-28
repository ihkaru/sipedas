<?php

namespace Tests\Unit;

require_once __DIR__.'/../TestCase.php';

use App\Models\TujuanSuratTugas;
use App\Supports\Constants;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class TujuanSuratTugasCombinerTest extends TestCase
{
    public function test_combiner_tujuan_handles_missing_master_sls_safely(): void
    {
        $masterSls = new Collection([
            (object) ['kec_id' => '6104010', 'kecamatan' => 'MEMPAWAH HILIR'],
        ]);

        $data = [
            ['level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_KECAMATAN, 'kecamatan_id' => '6104010'],
            ['level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_KECAMATAN, 'kecamatan_id' => '9999999'], // Not in master SLS
        ];

        $result = TujuanSuratTugas::combinerTujuan($data, $masterSls);

        $this->assertSame('Mempawah Hilir', $result);
    }

    public function test_combiner_tujuan_returns_dash_when_empty_or_all_missing(): void
    {
        $masterSls = new Collection;
        $data = [
            ['level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_KECAMATAN, 'kecamatan_id' => '9999999'],
        ];

        $result = TujuanSuratTugas::combinerTujuan($data, $masterSls);

        $this->assertSame('-', $result);
    }

    public function test_combiner_tujuan_returns_nama_tempat(): void
    {
        $masterSls = new Collection;
        $data = [
            (object) ['level_tujuan_penugasan' => Constants::LEVEL_PENUGASAN_NAMA_TEMPAT, 'nama_tempat_tujuan' => 'HOTEL GRAND MAHKOTA'],
        ];

        $result = TujuanSuratTugas::combinerTujuan($data, $masterSls);

        $this->assertSame('Hotel Grand Mahkota', $result);
    }
}
