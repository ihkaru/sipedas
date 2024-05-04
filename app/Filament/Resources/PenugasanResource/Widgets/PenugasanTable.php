<?php

namespace App\Filament\Resources\PenugasanResource\Widgets;

use App\Filament\Resources\PenugasanResource;
use App\Models\Penugasan;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
class PenugasanTable extends BaseWidget
{
    protected function getTableQuery(): Builder
    {
        return Penugasan::query();
    }
    public function table(Table $table): Table
    {
        $table = PenugasanResource::table($table);
        return $table
            ->query(
                $this->getTableQuery()->latest('created_at')
            )
            ->columns([
                TextColumn::make('pegawai.nama'),
                TextColumn::make('kegiatan.nama')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal Pengajuan')
            ])
            ;

    }
}
