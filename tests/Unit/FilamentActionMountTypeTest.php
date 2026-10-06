<?php

namespace Tests\Unit;

use App\Filament\Resources\PenugasanResource;
use App\Filament\Resources\PenugasanResource\Pages\ListPenugasans;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ReflectionFunction;
use Tests\TestCase;

class FilamentActionMountTypeTest extends TestCase
{
    public function test_no_closures_use_invalid_form_typehint_in_app(): void
    {
        $appPath = app_path();
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($appPath));

        $foundInvalid = [];
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                if (preg_match('/function\s*\([^)]*\bForm\s+\$/', $content)) {
                    $foundInvalid[] = str_replace($appPath . '/', '', $file->getPathname());
                }
            }
        }

        $this->assertEmpty(
            $foundInvalid,
            'Files containing invalid Form typehint: ' . implode(', ', $foundInvalid)
        );
    }

    public function test_penugasan_resource_actions_accept_schema(): void
    {
        $page = new ListPenugasans();
        $table = PenugasanResource::table(Table::make($page));

        $actions = $table->getActions();
        $lihatAction = null;
        $revisiAction = null;
        $ajukanRevisiAction = null;

        foreach ($actions as $action) {
            if ($action->getName() === 'lihat') {
                $lihatAction = $action;
            } elseif ($action->getName() === 'revisi') {
                $revisiAction = $action;
            } elseif ($action->getName() === 'ajukan_revisi') {
                $ajukanRevisiAction = $action;
            }
        }

        $this->assertNotNull($lihatAction, 'Action lihat must exist in PenugasanResource table');
        $this->assertNotNull($revisiAction, 'Action revisi must exist in PenugasanResource table');
        $this->assertNotNull($ajukanRevisiAction, 'Action ajukan_revisi must exist in PenugasanResource table');

        foreach ([$lihatAction, $revisiAction, $ajukanRevisiAction] as $action) {
            $mountClosure = $action->getMountUsing();
            $this->assertNotNull($mountClosure);

            $ref = new ReflectionFunction($mountClosure);
            $params = $ref->getParameters();
            $this->assertNotEmpty($params);

            $firstParamType = $params[0]->getType();
            $this->assertNotNull($firstParamType);
            $this->assertSame(
                Schema::class,
                $firstParamType->getName(),
                "Action {$action->getName()} mountUsing first parameter must be typed as " . Schema::class
            );
        }
    }
}
