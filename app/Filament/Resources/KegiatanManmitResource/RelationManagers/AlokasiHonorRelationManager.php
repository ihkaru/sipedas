<?php

namespace App\Filament\Resources\KegiatanManmitResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Honor;
use App\Models\Mitra;
use Carbon\CarbonPeriod;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Carbon;
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
                Tables\Columns\TextColumn::make('honor.harga_per_satuan')->money('IDR')->label('Harga per Satuan'),
                Tables\Columns\TextColumn::make('total_honor')->money('IDR')->sortable(),
                Tables\Columns\TextColumn::make('honor.tanggal_akhir_kegiatan')->date()->label('Tanggal akhir kegiatan')->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('alokasi_terpilih')
                    ->label('Alokasikan Honor')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn() => auth()->user()->can('create_alokasi::honor'))
                    ->form([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Section::make('Detail Honor')
                                ->schema([
                                    Forms\Components\Select::make('honor_id')
                                        ->label('Pilih Honor untuk Dialokasikan')
                                        ->options(fn(RelationManager $livewire): array => $this->getHonorOptions($livewire))
                                        ->required()
                                        ->live() // **PENTING**: Membuat field ini reaktif
                                        ->afterStateUpdated(function (Set $set) {
                                            // **BARU**: Reset daftar mitra jika honor diubah
                                            $set('mitra_to_add', null);
                                            $set('alokasi_data', []);
                                        }),

                                     Forms\Components\Select::make('mitra_to_add')
                                        ->label('Cari & Tambah Mitra')
                                        ->helperText('Mitra yang tidak layak (bentrok jadwal atau melebihi limit SBML) akan dinonaktifkan beserta alasannya.')
                                        ->searchable()
                                        ->getSearchResultsUsing(function (string $search, Get $get) {
                                            $honorId = $get('honor_id');
                                            if (!$honorId) return [];

                                            $honor = \App\Models\Honor::with('kegiatanManmit')->find($honorId);
                                            if (!$honor || !$honor->kegiatanManmit) return [];

                                            $year = \Illuminate\Support\Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan)->year;
                                            $isSensus = $honor->kegiatanManmit->jenis_kegiatan === 'SENSUS';
                                            $selectedMitraIds = collect($get('alokasi_data'))->pluck('mitra_id')->toArray();

                                            return \App\Models\Mitra::query()
                                                ->whereHas('kemitraans', function ($query) use ($year) {
                                                    $query->where('tahun', $year)
                                                        ->where('status', 'AKTIF');
                                                })
                                                ->whereNotIn('id', $selectedMitraIds)
                                                ->where(function($q) use ($search) {
                                                    $q->where('nama_1', 'like', "%{$search}%")
                                                      ->orWhere('id_sobat', 'like', "%{$search}%");
                                                })
                                                ->limit(50)
                                                ->get()
                                                ->mapWithKeys(function ($mitra) use ($honor, $isSensus) {
                                                    $res = \App\Services\HonorService::validateMitraEligibility(
                                                        $mitra->id,
                                                        $honor->kegiatanManmit->tgl_mulai_pelaksanaan,
                                                        $honor->kegiatanManmit->tgl_akhir_pelaksanaan,
                                                        0, 
                                                        $isSensus
                                                    );
                                                    
                                                    $rem = \App\Services\HonorService::getMitraRemainingBudget(
                                                        $mitra->id,
                                                        $honor->kegiatanManmit->tgl_mulai_pelaksanaan,
                                                        $honor->kegiatanManmit->tgl_akhir_pelaksanaan
                                                    );
                                                    
                                                    $sensusText = number_format($rem['sensus'] / 1000, 0) . 'rb';
                                                    $surveiText = number_format($rem['survei'] / 1000, 0) . 'rb';
                                                    
                                                    $label = "({$mitra->id_sobat}) {$mitra->nama_1} [Sisa S: Rp {$sensusText} | V: Rp {$surveiText}]";
                                                    
                                                    if (!$res['eligible']) {
                                                        $label .= " [❌ TIDAK LAYAK: {$res['message']}]";
                                                    }
                                                    
                                                    return [$mitra->id => $label];
                                                })
                                                ->toArray();
                                        })
                                        ->getOptionLabelUsing(fn ($value): ?string => \App\Models\Mitra::find($value)?->nama_1)
                                        ->disableOptionWhen(function ($value, Get $get) {
                                            $honorId = $get('honor_id');
                                            if (!$honorId) return true;

                                            $honor = \App\Models\Honor::with('kegiatanManmit')->find($honorId);
                                            if (!$honor || !$honor->kegiatanManmit) return true;

                                            $isSensus = $honor->kegiatanManmit->jenis_kegiatan === 'SENSUS';
                                            
                                            $res = \App\Services\HonorService::validateMitraEligibility(
                                                $value,
                                                $honor->kegiatanManmit->tgl_mulai_pelaksanaan,
                                                $honor->kegiatanManmit->tgl_akhir_pelaksanaan,
                                                0,
                                                $isSensus
                                            );
                                            return !$res['eligible'];
                                        })
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                            if (empty($state) || !is_numeric($state)) return;

                                            $repeaterItems = $get('alokasi_data') ?? [];
                                            if (!is_array($repeaterItems)) $repeaterItems = [];

                                            // Cek duplikasi sebelum menambahkan
                                            $mitraExists = collect($repeaterItems)->contains('mitra_id', $state);

                                            if (!$mitraExists) {
                                                $repeaterItems[] = [
                                                    'mitra_id' => (int)$state,
                                                    'target_per_satuan_honor' => 0
                                                ];
                                                $set('alokasi_data', $repeaterItems);
                                            }

                                            // Reset dropdown setelah mitra ditambahkan
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
                                                ->options(fn() => Mitra::pluck('nama_1', 'id')) // Opsi ini hanya untuk display
                                                ->getOptionLabelFromRecordUsing(fn($record) => "({$record->id_sobat}) {$record->nama_1}")
                                                ->disabled()
                                                ->required()
                                                ->dehydrated(true),
                                             Forms\Components\TextInput::make('target_per_satuan_honor')
                                                ->label('Target')
                                                ->numeric()
                                                ->required()
                                                ->default(0)
                                                ->minValue(0)
                                                ->live()
                                                ->rules([
                                                    fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $honorId = $get('../../honor_id'); 
                                                        if (!$honorId) return;
                                                        
                                                        $honor = \App\Models\Honor::with('kegiatanManmit')->find($honorId);
                                                        if (!$honor || !$honor->kegiatanManmit) return;
                                                        
                                                        $mitraId = $get('mitra_id');
                                                        $totalHonor = $honor->harga_per_satuan * $value;
                                                        $isSensus = $honor->kegiatanManmit->jenis_kegiatan === 'SENSUS';
                                                        
                                                        $res = \App\Services\HonorService::validateMitraEligibility(
                                                            $mitraId,
                                                            $honor->kegiatanManmit->tgl_mulai_pelaksanaan,
                                                            $honor->kegiatanManmit->tgl_akhir_pelaksanaan,
                                                            $totalHonor,
                                                            $isSensus
                                                        );
                                                        
                                                        if (!$res['eligible']) {
                                                            $fail($res['message']);
                                                        }
                                                    },
                                                ]),
                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->addable(false)
                                        ->deletable(true),
                                ])
                        ]),
                    ])
                    ->action(function (array $data, RelationManager $livewire) {
                        // ... Logika action tidak berubah ...
                        try {
                            if (empty($data['honor_id'])) {
                                throw new \Exception("Honor ID tidak boleh kosong.");
                            }
                            if (empty($data['alokasi_data'])) {
                                throw new \Exception("Tidak ada mitra yang dipilih untuk dialokasikan.");
                            }

                            // --- AWAL PERBAIKAN ---

                            // 1. Ambil instance model Honor yang dipilih di form.
                            // Kita perlukan ini sebagai "induk" untuk menyimpan alokasi.
                            $honor = \App\Models\Honor::find($data['honor_id']);
                            if (!$honor) {
                                throw new \Exception("Honor dengan ID {$data['honor_id']} tidak ditemukan.");
                            }

                            $createdCount = 0;
                            foreach ($data['alokasi_data'] as $alokasi) {
                                if (!isset($alokasi['mitra_id'], $alokasi['target_per_satuan_honor'])) {
                                    continue;
                                }

                                $mitra = \App\Models\Mitra::find($alokasi['mitra_id']);
                                if (!$mitra) {
                                    continue;
                                }

                                // Panggil metode terpusat dengan ID Sobat (logika ini sudah benar)
                                $newAlokasi = \App\Models\AlokasiHonor::createWithRelations(
                                    $mitra->id_sobat,
                                    $honor->id, // Kirim ID honor
                                    (float)$alokasi['target_per_satuan_honor']
                                );

                                // 2. Simpan AlokasiHonor baru melalui relasi langsungnya: Honor -> alokasiHonors()
                                // Ini adalah cara yang benar dan "Eloquent Way".
                                $honor->alokasiHonors()->save($newAlokasi);

                                $createdCount++;
                            }

                            if ($createdCount > 0) {
                                Notification::make()->title('Alokasi Berhasil')->body("Berhasil membuat {$createdCount} alokasi honor.")->success()->send();
                            } else {
                                Notification::make()->title('Tidak Ada Data Valid')->body('Tidak ada alokasi yang berhasil dibuat.')->warning()->send();
                            }
                        } catch (\Exception $e) {
                            // Tangkap error dari validasi di dalam model
                            Notification::make()->title('Error')->body('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    ->modalWidth('5xl'),
                Action::make('honor_baru')
                    ->label("Buat Honor Baru")
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(route("filament.a.resources.honors.create"))
                    ->visible(fn() => auth()->user()->can('create_honor')),

                // FIXED: Create ActionGroup with dynamic actions for contract printing
                ...collect($this->getDynamicPrintActions())
                    ->toArray(),
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

    protected function getDynamicPrintActions(): array
    {
        $kegiatanManmit = $this->ownerRecord;
        $actions = [];

        // Get unique months from honors related to this activity
        $uniqueMonths = \App\Models\Honor::where('kegiatan_manmit_id', $kegiatanManmit->id)
            ->whereNotNull('tanggal_akhir_kegiatan')
            ->selectRaw("DISTINCT YEAR(tanggal_akhir_kegiatan) as year, MONTH(tanggal_akhir_kegiatan) as month")
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // Create ActionGroup for contract printing if there are unique months
        if ($uniqueMonths->isNotEmpty()) {
            $contractActions = [];

            // Tambahkan tombol Kontrak Sensus Full jika jenisnya Sensus
            if ($kegiatanManmit->jenis_kegiatan === 'SENSUS') {
                $contractActions[] = Action::make('cetak_kontrak_full')
                    ->label('Kontrak Sensus (Seluruh Periode)')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('warning')
                    ->url(route('cetak.kontrak', [
                        'id_kegiatan_manmit' => $kegiatanManmit->id,
                        'full' => 1,
                    ]))
                    ->openUrlInNewTab();
            }

            foreach ($uniqueMonths as $item) {
                $date = \Illuminate\Support\Carbon::create($item->year, $item->month, 1);
                
                // Bedakan label, icon, dan warna untuk Sensus
                $isSensus = $kegiatanManmit->jenis_kegiatan === 'SENSUS';
                $label = ($isSensus ? 'Kontrak Sensus ' : 'Kontrak ') . $date->translatedFormat('F Y');
                $icon = $isSensus ? 'heroicon-o-document-duplicate' : 'heroicon-o-document-text';
                $color = $isSensus ? 'warning' : 'info';
                
                $actionName = 'cetak_kontrak_' . $item->year . '_' . $item->month;

                $contractActions[] = Action::make($actionName)
                    ->label($label)
                    ->icon($icon)
                    ->color($color)
                    ->url(route('cetak.kontrak', [
                        'tahun' => $item->year,
                        'bulan' => $item->month,
                        'id_kegiatan_manmit' => $kegiatanManmit->id,
                    ]))
                    ->openUrlInNewTab();
            }

            $actions[] = ActionGroup::make($contractActions)
                ->label('Cetak Kontrak')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->button();
        }

        // Add BAST printing action — bulan diambil dari honor.tanggal_akhir_kegiatan (bukan tgl_akhir_pelaksanaan)
        $actions[] = Action::make('cetakBast')
            ->label('Cetak BAST')
            ->icon('heroicon-o-document-check')
            ->color('success')
            ->url(function () use ($kegiatanManmit): string {
                // Ambil tanggal_akhir_kegiatan terbesar dari honor kegiatan ini
                // sebagai referensi bulan untuk filter dan PPK
                $tanggalAkhir = \App\Models\Honor::where('kegiatan_manmit_id', $kegiatanManmit->id)
                    ->max('tanggal_akhir_kegiatan');

                if ($tanggalAkhir) {
                    $tgl = Carbon::parse($tanggalAkhir);
                } else {
                    $tgl = Carbon::parse($kegiatanManmit->tgl_akhir_pelaksanaan ?? now());
                }

                return route('cetak.bast', [
                    'tahun'              => $tgl->year,
                    'bulan'              => $tgl->month,
                    'id_kegiatan_manmit' => $kegiatanManmit->id,
                ]);
            })
            ->openUrlInNewTab();

        return $actions;
    }
}
