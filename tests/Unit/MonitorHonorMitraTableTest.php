<?php

namespace Tests\Unit;

use App\Filament\Pages\MonitorHonorMitra;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Tests\TestCase;

class MonitorHonorMitraTableTest extends TestCase
{
    public function test_monitor_honor_mitra_table_configures_bulk_actions_without_error(): void
    {
        $page = new MonitorHonorMitra();
        $table = Table::make($page);
        $configuredTable = $page->table($table);

        $bulkActions = $configuredTable->getBulkActions();
        $this->assertNotEmpty($bulkActions);

        $group = $bulkActions[0];
        $this->assertInstanceOf(BulkActionGroup::class, $group);

        $actions = $group->getActions();
        $this->assertNotEmpty($actions);
        $this->assertInstanceOf(BulkAction::class, $actions[0]);
        $this->assertSame('export_selected', $actions[0]->getName());
    }
}
