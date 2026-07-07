<?php

namespace App\Filament\Resources\RiwayatPpkResource\Pages;

use App\Filament\Resources\RiwayatPpkResource;
use App\Models\RiwayatPpk;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;

class ListRiwayatPpks extends ListRecords
{
    protected static string $resource = RiwayatPpkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Riwayat PPK')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getSubheading(): ?string
    {
        $ppkAktif = RiwayatPpk::aktif()->with('pegawai')->first();

        if ($ppkAktif) {
            $mulai = $ppkAktif->tgl_mulai->translatedFormat('d M Y');
            return "PPK Aktif Saat Ini: {$ppkAktif->pegawai?->nama} — menjabat sejak {$mulai}";
        }

        return '⚠️ Tidak ada PPK aktif saat ini. Harap tambahkan data PPK segera.';
    }
}
