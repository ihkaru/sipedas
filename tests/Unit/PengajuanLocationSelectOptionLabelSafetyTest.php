<?php

namespace Tests\Unit;

require_once __DIR__ . '/../TestCase.php';

use App\Filament\Resources\PenugasanResource;
use App\Filament\Resources\PenugasanResource\Pages\ListPenugasans;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DbSchema;
use Tests\TestCase;

class PengajuanLocationSelectOptionLabelSafetyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DbSchema::create('master_sls', function ($table) {
            $table->string('prov_id')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('kabkot_id')->nullable();
            $table->string('kabkot')->nullable();
            $table->string('kec_id')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa_kel_id')->nullable();
            $table->string('desa_kel')->nullable();
        });

        DbSchema::create('mitras', function ($table) {
            $table->string('id_sobat')->nullable();
            $table->string('nama_1')->nullable();
        });

        DbSchema::create('pegawais', function ($table) {
            $table->string('nip')->nullable();
            $table->string('nama')->nullable();
        });
    }

    public function test_form_pengajuan_selects_have_safe_option_label_closures(): void
    {
        $page = new ListPenugasans();
        $schema = Schema::make($page)->components(PenugasanResource::formPengajuan());

        $monitoredFields = ['kabkot_ids', 'prov_ids', 'kecamatan_ids', 'desa_kel_ids', 'mitras', 'nips'];
        $foundCount = 0;

        foreach ($schema->getComponents(withHidden: true) as $component) {
            if ($component instanceof Select && in_array($component->getName(), $monitoredFields, true)) {
                $foundCount++;

                // Ensure component has safe custom closures for getOptionLabelUsing and getOptionLabelsUsing
                $reflection = new \ReflectionClass($component);

                $labelProp = $reflection->getProperty('getOptionLabelUsing');
                $labelProp->setAccessible(true);
                $this->assertNotNull(
                    $labelProp->getValue($component),
                    "Select component '{$component->getName()}' must define getOptionLabelUsing to prevent array offset TypeError"
                );

                $labelsProp = $reflection->getProperty('getOptionLabelsUsing');
                $labelsProp->setAccessible(true);
                $this->assertNotNull(
                    $labelsProp->getValue($component),
                    "Select component '{$component->getName()}' must define getOptionLabelsUsing"
                );

                // Simulate empty array state (Livewire synth format [[], {"s":"arr"}])
                $component->state([]);
                $label = $component->getOptionLabel();
                $this->assertIsString(
                    $label ?? '',
                    "getOptionLabel on empty array state for '{$component->getName()}' must return string without throwing"
                );

                // Simulate non-empty array state
                $component->state(['6104']);
                $labelWithVal = $component->getOptionLabel();
                $this->assertIsString(
                    $labelWithVal ?? '',
                    "getOptionLabel on array state for '{$component->getName()}' must return string without throwing"
                );
            }
        }

        $this->assertSame(
            count($monitoredFields),
            $foundCount,
            'All monitored fields must be present and verified in formPengajuan'
        );
    }
}
