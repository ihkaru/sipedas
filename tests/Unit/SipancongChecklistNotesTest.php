<?php

namespace Tests\Unit;

use App\Filament\Resources\Sipancong\PengajuanResource\Forms\PengajuanForms;
use App\Services\Sipancong\PengajuanServices;
use Tests\TestCase;

class SipancongChecklistNotesTest extends TestCase
{
    public function test_checklist_item_definitions_cover_all_six_required_documents(): void
    {
        $definitions = PengajuanForms::checklistItemDefinitions();

        $this->assertSame(
            ['kak', 'nominatif', 'fp', 'bukti_pengeluaran', 'sk', 'bukti_lainnya'],
            array_keys($definitions)
        );
        $this->assertArrayHasKey('TIDAK_ADA', $definitions['sk']['options']);
        $this->assertArrayHasKey('PERLU_PERBAIKAN', $definitions['sk']['options']);
    }

    public function test_build_structured_checklist_notes_returns_null_when_all_sesuai_and_no_general_note(): void
    {
        $note = PengajuanServices::buildStructuredChecklistNotes('ppk', [
            'checklist_kak' => 'SESUAI',
            'checklist_nominatif' => 'SESUAI',
            'checklist_fp' => 'SESUAI',
            'checklist_bukti_pengeluaran' => 'SESUAI',
            'checklist_sk' => 'ADA_SESUAI',
            'checklist_bukti_lainnya' => 'SESUAI',
            'catatan_ppk' => '',
        ]);

        $this->assertNull($note);
    }

    public function test_build_structured_checklist_notes_formats_revisions_and_general_note(): void
    {
        $note = PengajuanServices::buildStructuredChecklistNotes('bendahara', [
            'checklist_kak' => 'PERLU_PERBAIKAN',
            'catatan_checklist_kak' => 'Tanggal kegiatan belum sesuai',
            'checklist_nominatif' => 'SESUAI',
            'checklist_fp' => 'SESUAI',
            'checklist_bukti_pengeluaran' => 'PERLU_PERBAIKAN',
            'catatan_checklist_bukti_pengeluaran' => 'Kuitansi belum ditandatangani',
            'checklist_sk' => 'TIDAK_ADA',
            'catatan_checklist_sk' => 'Mohon lampirkan SK tim',
            'checklist_bukti_lainnya' => 'SESUAI',
            'catatan_bendahara' => 'Segera dilengkapi hari ini',
        ]);

        $this->assertNotNull($note);
        $this->assertStringContainsString('- KAK (Perlu Perbaikan): Tanggal kegiatan belum sesuai', $note);
        $this->assertStringContainsString('- Bukti Pengeluaran (Perlu Perbaikan): Kuitansi belum ditandatangani', $note);
        $this->assertStringContainsString('- SK (Tidak Ada): Mohon lampirkan SK tim', $note);
        $this->assertStringContainsString('Catatan Tambahan: Segera dilengkapi hari ini', $note);
        $this->assertStringNotContainsString('Nominatif', $note);
    }
}
