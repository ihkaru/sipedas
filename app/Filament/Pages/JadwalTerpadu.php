<?php

namespace App\Filament\Pages;

use App\Models\UnifiedMilestone;
use App\Models\UnifiedMetric;
use App\Services\GoogleSheetsService;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Exception;

class JadwalTerpadu extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.jadwal-terpadu';

    protected static ?string $navigationLabel = 'Jadwal Terpadu';

    protected static ?string $title = 'Jadwal Terpadu';

    protected static ?string $navigationGroup = 'Content Management';

    public array $milestones = [];
    public array $metrics = [];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->milestones = UnifiedMilestone::orderBy('tanggal', 'asc')->get()->toArray();
        $this->metrics = UnifiedMetric::all()->toArray();
    }

    public function sync(GoogleSheetsService $sheetsService)
    {
        try {
            $result = $sheetsService->sync();
            $this->loadData();
            
            Notification::make()
                ->title('Sinkronisasi Berhasil')
                ->body("Berhasil menyinkronkan {$result['milestones_count']} milestone dan {$result['metrics_count']} metrik dari Google Sheet.")
                ->success()
                ->send();

            // Dispatch browser event to refresh Javascript data
            $this->dispatch('sheets-synced',
                milestones: $this->milestones,
                metrics: $this->metrics
            );

        } catch (Exception $e) {
            Notification::make()
                ->title('Sinkronisasi Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
