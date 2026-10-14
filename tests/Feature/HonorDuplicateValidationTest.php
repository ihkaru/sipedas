<?php

namespace Tests\Feature;

use App\Models\Honor;
use App\Models\KegiatanManmit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HonorDuplicateValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        KegiatanManmit::create([
            'id' => 'K3',
            'nama' => 'Kegiatan K3 2026',
            'jenis_kegiatan' => 'SURVEI',
            'tgl_mulai_pelaksanaan' => '2026-07-01',
            'tgl_akhir_pelaksanaan' => '2026-09-30',
        ]);
    }

    public function test_honor_creation_succeeds_for_unique_combination(): void
    {
        $honor = Honor::create([
            'kegiatan_manmit_id' => 'K3',
            'jabatan' => 'PETUGAS ENTRI',
            'jenis_honor' => 'PENGOLAHAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 11000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        $this->assertEquals('K3-PETUGAS ENTRI-PENGOLAHAN', $honor->id);
        $this->assertDatabaseHas('honors', [
            'id' => 'K3-PETUGAS ENTRI-PENGOLAHAN',
            'kegiatan_manmit_id' => 'K3',
            'jabatan' => 'PETUGAS ENTRI',
            'jenis_honor' => 'PENGOLAHAN',
        ]);
    }

    public function test_duplicate_honor_creation_throws_validation_exception_instead_of_sql_error(): void
    {
        // First entry
        Honor::create([
            'kegiatan_manmit_id' => 'K3',
            'jabatan' => 'PETUGAS ENTRI',
            'jenis_honor' => 'PENGOLAHAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 11000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);

        // Attempting to create duplicate entry must throw ValidationException
        $this->expectException(ValidationException::class);

        Honor::create([
            'kegiatan_manmit_id' => 'K3',
            'jabatan' => 'PETUGAS ENTRI',
            'jenis_honor' => 'PENGOLAHAN',
            'satuan_honor' => 'DOKUMEN',
            'harga_per_satuan' => 11000,
            'tanggal_akhir_kegiatan' => '2026-09-30',
        ]);
    }
}
