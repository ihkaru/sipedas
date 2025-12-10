<?php

namespace App\Filament\Resources\KegiatanManmitResource\Pages;

use App\Filament\Resources\KegiatanManmitResource;
use App\Models\KegiatanManmit;
use App\Supports\Constants;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateKegiatanManmit extends CreateRecord
{
    protected static string $resource = KegiatanManmitResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $baseId = Constants::getTextInParentheses($data['nama'] ?? '');
        $baseName = $data['nama'];
        $frekuensi = $data['frekuensi_kegiatan'];
        $createdOrUpdatedModels = [];

        // Data yang sama untuk semua kasus tunggal
        $commonData = [
            'nama' => $baseName,
            'frekuensi_kegiatan' => $frekuensi,
        ];

        // Kasus 1: Frekuensi berulang (Bulanan, Triwulanan, Semesteran)
        if (isset($data['periods'])) {
            $periodOptions = [];
            switch ($frekuensi) {
                case Constants::FREKUENSI_TRIWULANAN:
                    $periodOptions = Constants::TRIWULAN_OPTIONS;
                    break;
                case Constants::FREKUENSI_BULANAN:
                    $periodOptions = Constants::BULAN_OPTIONS;
                    break;
                case Constants::FREKUENSI_SEMESTERAN:
                    $periodOptions = Constants::SEMESTER_OPTIONS;
                    break;
                case Constants::FREKUENSI_SUBROUND:
                    $periodOptions = Constants::SUBROUND_OPTIONS;
                    break;
            }

            foreach ($data['periods'] as $periodData) {
                $periodKey = $periodData['period_key'];
                $periodName = $periodData['period_name'];
                $periodSlug = Str::slug($periodName);

                $createdOrUpdatedModels[] = KegiatanManmit::updateOrCreate(
                    [
                        'id' => "{$baseId}-{$periodKey}-{$periodSlug}", // Kunci pencarian
                    ],
                    [ // Data untuk di-update atau di-create
                        'nama' => "{$baseName} {$periodName}",
                        'tgl_mulai_pelaksanaan' => $periodData['tgl_mulai'],
                        'tgl_akhir_pelaksanaan' => $periodData['tgl_akhir'],
                        'frekuensi_kegiatan' => $frekuensi,
                    ]
                );
            }
        }
        // Kasus 2: Frekuensi tunggal (Tahunan, Periodik, Adhoc)
        else {
            $id = $baseId;
            if($frekuensi == Constants::FREKUENSI_ADHOC){
                $id = $baseId .'-'. Str::uuid();
            }
            $createdOrUpdatedModels[] = KegiatanManmit::updateOrCreate(
                [
                    'id' => $id, // Kunci pencarian
                ],
                array_merge($commonData, [ // Data untuk di-update atau di-create
                    'tgl_mulai_pelaksanaan' => $data['tgl_mulai_pelaksanaan'],
                    'tgl_akhir_pelaksanaan' => $data['tgl_akhir_pelaksanaan'],
                ])
            );
        }

        // Mengembalikan model pertama untuk redirect setelah create
        return $createdOrUpdatedModels[0] ?? new ($this->getModel());
    }
}
