<?php

namespace App\Filament\Resources\KegiatanManmitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Honor;
use App\Models\Mitra;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AlokasiHonorRelationManager extends RelationManager
{
    protected static string $relationship = 'alokasiHonors';
    protected static ?string $title = 'Alokasi Honor Mitra';

    // Form untuk Edit Action (tidak berubah)
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('mitra_id')
                    ->relationship('mitra', 'nama_1')
                    ->getOptionLabelFromRecordUsing(fn(Mitra $record) => "({$record->id_sobat}) {$record->nama_1}")
                    ->disabled(),
                Forms\Components\Select::make('honor_id')
                    ->options(fn(RelationManager $livewire): array => $this->getHonorOptions($livewire))
                    ->disabled(),
                Forms\Components\TextInput::make('target_per_satuan_honor')
                    ->label('Target per Satuan')
                    ->numeric()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('mitra.id_sobat')->label('ID Sobat')->searchable(),
                Tables\Columns\TextColumn::make('mitra.nama_1')->label('Nama Mitra')->searchable(),
                Tables\Columns\TextColumn::make('honor.jabatan')->label('Jabatan'),
                Tables\Columns\TextColumn::make('honor.jenis_honor')->label('Jenis Honor'),
                Tables\Columns\TextColumn::make('target_per_satuan_honor')->label('Target'),
                Tables\Columns\TextColumn::make('honor.satuan_honor')->label('Satuan'),
                Tables\Columns\TextColumn::make('total_honor')->money('IDR')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('alokasi_terpilih')
                    ->label('Buat Alokasi Baru')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Section::make('Detail Honor')
                                ->schema([
                                    Forms\Components\Select::make('honor_id')
                                        ->label('Pilih Honor untuk Dialokasikan')
                                        ->options(function (RelationManager $livewire) {
                                            $options = $this->getHonorOptions($livewire);
                                            Log::info('Honor options loaded:', $options);
                                            return $options;
                                        })
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state) {
                                            Log::info('Honor selected:', ['honor_id' => $state]);
                                        }),

                                    Forms\Components\Select::make('mitra_to_add')
                                        ->label('Cari & Tambah Mitra')
                                        ->helperText('Pilih mitra dari daftar ini untuk menambahkannya ke daftar alokasi di sebelah kanan.')
                                        ->options(function () {
                                            return Mitra::query()
                                                ->get()
                                                ->mapWithKeys(function ($mitra) {
                                                    return [$mitra->id => "({$mitra->id_sobat}) {$mitra->nama_1}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            if (empty($state) || !is_numeric($state)) {
                                                return;
                                            }

                                            $repeaterItems = $get('alokasi_data') ?? [];

                                            // Pastikan $repeaterItems adalah array
                                            if (!is_array($repeaterItems)) {
                                                $repeaterItems = [];
                                            }

                                            // Cek apakah mitra sudah ada
                                            $mitraExists = false;
                                            foreach ($repeaterItems as $item) {
                                                if (isset($item['mitra_id']) && $item['mitra_id'] == $state) {
                                                    $mitraExists = true;
                                                    break;
                                                }
                                            }

                                            if (!$mitraExists) {
                                                $repeaterItems[] = [
                                                    'mitra_id' => (int)$state,
                                                    'target_per_satuan_honor' => 0
                                                ];
                                                $set('alokasi_data', $repeaterItems);
                                            }

                                            $set('mitra_to_add', null);
                                        })
                                        ->dehydrated(false),
                                ]),

                            Forms\Components\Section::make('Daftar Mitra untuk Dialokasikan')
                                ->schema([
                                    Forms\Components\Repeater::make('alokasi_data')
                                        ->label('')
                                        ->defaultItems(0)
                                        ->schema([
                                            Forms\Components\Select::make('mitra_id')
                                                ->label('Mitra')
                                                ->options(function () {
                                                    return Mitra::query()
                                                        ->get()
                                                        ->mapWithKeys(function ($mitra) {
                                                            return [$mitra->id => "({$mitra->id_sobat}) {$mitra->nama_1}"];
                                                        })
                                                        ->toArray();
                                                })
                                                ->disabled()
                                                ->required()
                                                // Pastikan data tetap terkirim meski disabled
                                                ->dehydrated(true),
                                            Forms\Components\TextInput::make('target_per_satuan_honor')
                                                ->label('Target')
                                                ->numeric()
                                                ->required()
                                                ->default(0)
                                                ->minValue(0),
                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->addable(false)
                                        ->deletable(true),
                                ])
                        ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        try {
                            if (empty($data['honor_id'])) {
                                throw new \Exception("Honor ID tidak boleh kosong.");
                            }
                            $honor = \App\Models\Honor::find($data['honor_id']);
                            if (!$honor) {
                                throw new \Exception("Honor dengan ID {$data['honor_id']} tidak ditemukan.");
                            }
                            if (empty($data['alokasi_data'])) {
                                throw new \Exception("Tidak ada mitra yang dipilih.");
                            }

                            $createdCount = 0;
                            foreach ($data['alokasi_data'] as $alokasi) {
                                if (!isset($alokasi['mitra_id'], $alokasi['target_per_satuan_honor'])) continue;

                                $mitraId = $alokasi['mitra_id'];
                                $mitra = \App\Models\Mitra::find($mitraId);
                                if (!$mitra) continue;

                                // --- LOGIKA PEMBUATAN NOMOR SURAT DIMASUKKAN DI SINI ---

                                // 1. Tentukan tanggal-tanggal penting
                                $tanggalAkhirKegiatan = $honor->tanggal_akhir_kegiatan;

                                // Asumsi tanggal pengajuan BAST adalah hari kerja sebelum/pada tanggal akhir kegiatan
                                $tanggalPengajuanBast = \App\Supports\TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan, -1);

                                // Asumsi tanggal pengajuan SPK adalah hari kerja pertama di bulan yang sama dengan tanggal akhir kegiatan
                                $tanggalPengajuanSpk = \App\Supports\TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan->copy()->startOfMonth());

                                // 2. Generate Nomor Surat Kontrak (SPK) - Satu per mitra per bulan
                                $existingSpkId = \App\Models\AlokasiHonor::where('mitra_id', $mitraId)
                                    ->whereHas('honor', function ($query) use ($tanggalAkhirKegiatan) {
                                        $query->whereYear('tanggal_akhir_kegiatan', $tanggalAkhirKegiatan->year)
                                            ->whereMonth('tanggal_akhir_kegiatan', $tanggalAkhirKegiatan->month);
                                    })
                                    ->whereNotNull('surat_perjanjian_kerja_id')
                                    ->value('surat_perjanjian_kerja_id');

                                if ($existingSpkId) {
                                    $suratPerjanjianKerjaId = $existingSpkId;
                                } else {
                                    $nomorSuratSpk = \App\Models\NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk);
                                    $suratPerjanjianKerjaId = $nomorSuratSpk->id;
                                }

                                // 3. Generate Nomor Surat BAST - Satu per mitra per kegiatan per bulan
                                // (Dalam UI ini, honor sudah unik, jadi ini efektif per alokasi)
                                // Logika sebelumnya mencari berdasarkan id_kegiatan, kita adaptasi
                                $existingBastId = \App\Models\AlokasiHonor::where('mitra_id', $mitraId)
                                    ->where('honor_id', $honor->id)
                                    ->whereNotNull('surat_bast_id')
                                    ->value('surat_bast_id');

                                if ($existingBastId) {
                                    $suratBastId = $existingBastId;
                                } else {
                                    $nomorSuratBast = \App\Models\NomorSurat::generateNomorSuratBast($tanggalPengajuanBast);
                                    $suratBastId = $nomorSuratBast->id;
                                }

                                // 4. Hitung total honor dan simpan
                                $totalHonor = $honor->harga_per_satuan * $alokasi['target_per_satuan_honor'];

                                $livewire->ownerRecord->alokasiHonors()->create([
                                    'honor_id' => $data['honor_id'],
                                    'mitra_id' => $mitraId,
                                    'target_per_satuan_honor' => $alokasi['target_per_satuan_honor'],
                                    'total_honor' => $totalHonor,
                                    'surat_perjanjian_kerja_id' => $suratPerjanjianKerjaId,
                                    'surat_bast_id' => $suratBastId,
                                    'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk, // Simpan tanggal ini
                                    'tanggal_mulai_perjanjian' => $tanggalAkhirKegiatan->copy()->startOfMonth(),
                                    'tanggal_akhir_perjanjian' => $tanggalAkhirKegiatan->copy()->endOfMonth(),
                                ]);

                                $createdCount++;
                            }

                            if ($createdCount > 0) {
                                \Filament\Notifications\Notification::make()->title('Alokasi Berhasil')->body("Berhasil membuat {$createdCount} alokasi honor.")->success()->send();
                            } else {
                                \Filament\Notifications\Notification::make()->title('Tidak Ada Data Valid')->body('Tidak ada alokasi yang berhasil dibuat.')->warning()->send();
                            }
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()->title('Error')->body('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    ->modalWidth('5xl'),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    protected function getHonorOptions(RelationManager $livewire): array
    {
        try {
            Log::info('Getting honor options for owner record:', ['owner_id' => $livewire->ownerRecord->id]);

            $honors = $livewire->ownerRecord->honors()->get();
            Log::info('Found honors:', ['count' => $honors->count()]);

            $options = $honors->mapWithKeys(function ($honor) {
                $label = "({$honor->jabatan}) - {$honor->jenis_honor} - {$honor->satuan_honor}";
                Log::info('Honor option:', ['id' => $honor->id, 'label' => $label]);
                return [$honor->id => $label];
            })->toArray();

            return $options;
        } catch (\Exception $e) {
            Log::error('Error getting honor options:', ['error' => $e->getMessage()]);
            return [];
        }
    }
}
