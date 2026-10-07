<?php

namespace App\Filament\Resources\ApiKeyResource\Pages;

use App\Filament\Resources\ApiKeyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApiKey extends EditRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('copy_ai_context')
                ->label('Copy AI Context')
                ->icon('heroicon-o-sparkles')
                ->color('info')
                ->modalHeading(fn () => "Konteks AI & Kunci API: {$this->record->name}")
                ->modalDescription('Prompt sistem lengkap yang siap ditempelkan langsung ke coding agent (Claude, Cursor, Windsurf, ChatGPT, dll).')
                ->modalWidth('7xl')
                ->modalContent(fn () => view('filament.resources.api-key-resource.modals.ai-context', [
                    'record' => $this->record,
                    'context' => \App\Services\AiContextService::generateContextForApiKey($this->record),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),
            Actions\DeleteAction::make(),
        ];
    }
}
