<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncGoogleSheets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sikendis:sync-sheets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync unified_milestones and unified_metrics tabs from Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\GoogleSheetsService $sheetsService)
    {
        $this->info('Starting Google Sheets sync...');
        
        try {
            $result = $sheetsService->sync();
            $this->info("Sync completed successfully!");
            $this->table(['Metric', 'Count'], [
                ['Milestones synced', $result['milestones_count']],
                ['Metrics synced', $result['metrics_count']],
            ]);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Sync failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
