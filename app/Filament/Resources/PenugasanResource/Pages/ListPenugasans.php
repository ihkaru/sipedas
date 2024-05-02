<?php

namespace App\Filament\Resources\PenugasanResource\Pages;

use App\Filament\Resources\PenugasanResource;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;

class ListPenugasans extends ListRecords
{
    protected static string $resource = PenugasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make("tambah_pengajuan")
                    ->label("Pengajuan Surat Tugas")
                    ->icon("fluentui-document-add-20-o")
                    ->form([
                        Select::make("nip")
                            ->label("Pegawai")
                            ->relationship('pegawai','nip')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Pegawai $record)=>$record->nama)
                            ->searchable(['nama'])
                            ->multiple(),
                        Select::make("kegiatan_id")
                            ->label("Kegiatan")
                            ->relationship('kegiatan','id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                            ->searchable(['nama']),
                        DatePicker::make('tgl_mulai_tugas')
                            ->label("Tanggal Mulai Penugasan"),
                        DatePicker::make('tgl_selesai_tugas')
                            ->label("Tanggal Selesai Penugasan"),
                        TextInput::make("tbh_awal_perjalan")
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Awal Perjalanan"),
                        TextInput::make("tbh_akhir_perjalan")
                            ->placeholder("Masukan tanggal")
                            ->numeric()
                            ->label("Tambah Hari Akhir Perjalanan"),
                        Select::make("prov_id")
                            ->label("Provinsi")
                            ->relationship('kecamatan','prov_id')
                            ->searchDebounce(100)
                            ->getOptionLabelFromRecordUsing(fn(Kegiatan $record)=>$record->nama)
                            ->searchable(['nama']),

                    ]),
        ];
    }
}
