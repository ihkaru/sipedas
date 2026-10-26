<?php

namespace Tests\Unit;

require_once __DIR__ . '/../TestCase.php';

use App\Models\Sipancong\JenisDokumen;
use App\Models\Sipancong\Pengajuan;
use App\Models\Sipancong\PosisiDokumen;
use App\Models\Sipancong\StatusPembayaran;
use App\Models\Sipancong\StatusPengajuan;
use App\Models\Sipancong\Subfungsi;
use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Tests\TestCase;

class PengajuanRelationshipSafetyTest extends TestCase
{
    public function test_pengajuan_model_defines_all_parent_relations_as_belongs_to(): void
    {
        $pengajuan = new Pengajuan();

        // penanggungJawab must be BelongsTo, never HasOne
        $penanggungJawab = $pengajuan->penanggungJawab();
        $this->assertInstanceOf(
            BelongsTo::class,
            $penanggungJawab,
            'penanggungJawab must be BelongsTo so Filament does not attempt to dissociate related Pegawai with null foreign key'
        );
        $this->assertNotInstanceOf(HasOne::class, $penanggungJawab);
        $this->assertSame('nip_penanggung_jawab', $penanggungJawab->getForeignKeyName());
        $this->assertSame('nip', $penanggungJawab->getOwnerKeyName());

        // pegawai / pengaju must be BelongsTo
        $pegawai = $pengajuan->pegawai();
        $this->assertInstanceOf(BelongsTo::class, $pegawai);
        $this->assertNotInstanceOf(HasOne::class, $pegawai);
        $this->assertSame('nip_pengaju', $pegawai->getForeignKeyName());
        $this->assertSame('nip', $pegawai->getOwnerKeyName());

        $pengaju = $pengajuan->pengaju();
        $this->assertInstanceOf(BelongsTo::class, $pengaju);
        $this->assertNotInstanceOf(HasOne::class, $pengaju);

        // lookup status & taxonomy relations
        $posisiDokumen = $pengajuan->posisiDokumen();
        $this->assertInstanceOf(BelongsTo::class, $posisiDokumen);
        $this->assertSame('posisi_dokumen_id', $posisiDokumen->getForeignKeyName());

        $subfungsi = $pengajuan->subfungsi();
        $this->assertInstanceOf(BelongsTo::class, $subfungsi);
        $this->assertSame('sub_fungsi_id', $subfungsi->getForeignKeyName());

        $statusPembayaran = $pengajuan->statusPembayaran();
        $this->assertInstanceOf(BelongsTo::class, $statusPembayaran);
        $this->assertSame('status_pembayaran_id', $statusPembayaran->getForeignKeyName());

        $jenisDokumen = $pengajuan->jenisDokumen();
        $this->assertInstanceOf(BelongsTo::class, $jenisDokumen);
        $this->assertSame('jenis_dokumen_id', $jenisDokumen->getForeignKeyName());

        $statusPengajuanPpk = $pengajuan->statusPengajuanPpk();
        $this->assertInstanceOf(BelongsTo::class, $statusPengajuanPpk);
        $this->assertSame('status_pengajuan_ppk_id', $statusPengajuanPpk->getForeignKeyName());

        $statusPengajuanPpspm = $pengajuan->statusPengajuanPpspm();
        $this->assertInstanceOf(BelongsTo::class, $statusPengajuanPpspm);
        $this->assertSame('status_pengajuan_ppspm_id', $statusPengajuanPpspm->getForeignKeyName());

        $statusPengajuanBendahara = $pengajuan->statusPengajuanBendahara();
        $this->assertInstanceOf(BelongsTo::class, $statusPengajuanBendahara);
        $this->assertSame('status_pengajuan_bendahara_id', $statusPengajuanBendahara->getForeignKeyName());
    }

    public function test_pengajuan_resource_actions_use_attributes_to_array_for_mount_using(): void
    {
        $resourceFile = file_get_contents(app_path('Filament/Resources/Sipancong/PengajuanResource.php'));

        $this->assertStringNotContainsString(
            '$form->fill($record->toArray())',
            $resourceFile,
            'PengajuanResource action modal mountUsing must not use toArray() which dumps eager-loaded relations into form state'
        );

        $this->assertStringContainsString(
            '$form->fill($record->attributesToArray())',
            $resourceFile,
            'PengajuanResource action modal mountUsing must use attributesToArray()'
        );
    }
}
