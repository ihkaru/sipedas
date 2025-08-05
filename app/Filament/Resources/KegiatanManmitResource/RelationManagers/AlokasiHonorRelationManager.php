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
                            // Debug: Log data yang diterima
                            Log::info('Form data received:', $data);

                            // Validasi honor_id dengan logging
                            if (!isset($data['honor_id'])) {
                                Log::error('honor_id not set in form data');
                                Notification::make()
                                    ->title('Error')
                                    ->body('Honor ID tidak ditemukan dalam form data.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            if (empty($data['honor_id'])) {
                                Log::error('honor_id is empty', ['honor_id' => $data['honor_id']]);
                                Notification::make()
                                    ->title('Error')
                                    ->body('Honor ID kosong. Silakan pilih honor terlebih dahulu.')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $honor = Honor::find($data['honor_id']);
                            if (!$honor) {
                                Log::error('Honor not found', ['honor_id' => $data['honor_id']]);
                                Notification::make()
                                    ->title('Error')
                                    ->body("Honor dengan ID {$data['honor_id']} tidak ditemukan.")
                                    ->danger()
                                    ->send();
                                return;
                            }

                            Log::info('Honor found successfully', ['honor_id' => $honor->id, 'jabatan' => $honor->jabatan]);

                            // Validasi alokasi_data
                            if (empty($data['alokasi_data']) || !is_array($data['alokasi_data'])) {
                                Log::error('alokasi_data is empty or not array', ['alokasi_data' => $data['alokasi_data'] ?? 'null']);
                                Notification::make()
                                    ->title('Tidak Ada Data')
                                    ->body('Anda belum memilih mitra untuk dialokasikan.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $createdCount = 0;
                            foreach ($data['alokasi_data'] as $index => $alokasi) {
                                Log::info("Processing alokasi item {$index}:", $alokasi);

                                // Validasi setiap item alokasi
                                if (!isset($alokasi['mitra_id'])) {
                                    Log::warning("Missing mitra_id in alokasi item {$index}");
                                    continue;
                                }

                                if (!isset($alokasi['target_per_satuan_honor'])) {
                                    Log::warning("Missing target_per_satuan_honor in alokasi item {$index}");
                                    continue;
                                }

                                if (empty($alokasi['mitra_id']) || !is_numeric($alokasi['target_per_satuan_honor'])) {
                                    Log::warning("Invalid data in alokasi item {$index}", [
                                        'mitra_id' => $alokasi['mitra_id'] ?? 'null',
                                        'target_per_satuan_honor' => $alokasi['target_per_satuan_honor'] ?? 'null'
                                    ]);
                                    continue;
                                }

                                $totalHonor = $honor->harga_per_satuan * $alokasi['target_per_satuan_honor'];

                                $livewire->ownerRecord->alokasiHonors()->create([
                                    'honor_id' => $data['honor_id'],
                                    'mitra_id' => $alokasi['mitra_id'],
                                    'target_per_satuan_honor' => $alokasi['target_per_satuan_honor'],
                                    'total_honor' => $totalHonor
                                ]);

                                $createdCount++;
                            }

                            if ($createdCount > 0) {
                                Notification::make()
                                    ->title('Alokasi Berhasil')
                                    ->body("Berhasil membuat {$createdCount} alokasi honor.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Tidak Ada Data Valid')
                                    ->body('Tidak ada alokasi yang berhasil dibuat.')
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error')
                                ->body('Terjadi kesalahan: ' . $e->getMessage())
                                ->danger()
                                ->send();
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
