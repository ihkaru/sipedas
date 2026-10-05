<?php

namespace Tests\Unit;

use App\Filament\Resources\PenugasanResource;
use App\Filament\Resources\PenugasanResource\Pages\ListPenugasans;
use App\Filament\Resources\PenugasanResource\Widgets\PenugasanTable;
use App\Filament\Resources\Sipancong\PengajuanResource;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Tests\TestCase;

class FilamentSchemaActionMountTest extends TestCase
{
    public function test_penugasan_table_header_action_uses_schema_typehint(): void
    {
        $widget = new PenugasanTable();
        $ref = new \ReflectionMethod($widget, 'getTableHeaderActions');
        $actions = $ref->invoke($widget);

        $found = false;
        foreach ($actions as $action) {
            if ($action->getName() === 'tambah_pengajuan') {
                $found = true;
                $closure = $action->getMountUsing();
                $this->assertInstanceOf(\Closure::class, $closure);
                $closureRef = new \ReflectionFunction($closure);
                $params = $closureRef->getParameters();
                $this->assertNotEmpty($params);
                $this->assertEquals(Schema::class, (string) $params[0]->getType()?->getName());
            }
        }
        $this->assertTrue($found, 'Action tambah_pengajuan not found');
    }

    public function test_list_penugasans_header_action_uses_schema_typehint(): void
    {
        $page = new ListPenugasans();
        $ref = new \ReflectionMethod($page, 'getHeaderActions');
        $actions = $ref->invoke($page);

        $found = false;
        foreach ($actions as $action) {
            if ($action->getName() === 'tambah_pengajuan') {
                $found = true;
                $closure = $action->getMountUsing();
                $this->assertInstanceOf(\Closure::class, $closure);
                $closureRef = new \ReflectionFunction($closure);
                $params = $closureRef->getParameters();
                $this->assertNotEmpty($params);
                $this->assertEquals(Schema::class, (string) $params[0]->getType()?->getName());
            }
        }
        $this->assertTrue($found, 'Action tambah_pengajuan not found in ListPenugasans');
    }

    public function test_all_codebase_mount_using_closures_do_not_use_undefined_form(): void
    {
        $directory = new \RecursiveDirectoryIterator(app_path('Filament'));
        $iterator = new \RecursiveIteratorIterator($directory);
        $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        foreach ($regex as $file => $item) {
            $content = file_get_contents($file);
            $this->assertDoesNotMatchRegularExpression(
                '/mountUsing\s*\(\s*function\s*\(\s*Form\s+\$/',
                $content,
                "Found unresolved 'Form $' typehint in mountUsing in file: {$file}"
            );
        }
    }
}
