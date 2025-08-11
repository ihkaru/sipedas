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
                Tables\Columns\TextColumn::make('tanggal_akhir_perjanjian')->date()->sortable(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('alokasi_terpilih')
                    ->label('Alokasikan Honor')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
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
                                        ->helperText('Hanya mitra dengan status kemitraan AKTIF pada tahun kegiatan yang akan muncul.') // Teks bantuan diperbarui
                                        // **LOGIKA UTAMA**: Opsi mitra sekarang dinamis berdasarkan honor_id
                                        ->options(function (Get $get) {
                                            $honorId = $get('honor_id');
                                            if (!$honorId) {
                                                return []; // Jika belum ada honor, jangan tampilkan mitra
                                            }

                                            // 1. Dapatkan honor untuk menemukan tahun kegiatan
                                            $honor = Honor::with('kegiatanManmit')->find($honorId);

                                            // Pastikan relasi dan tanggal ada
                                            if (!$honor || !$honor->kegiatanManmit?->tgl_mulai_pelaksanaan) {
                                                return [];
                                            }

                                            // 2. Ekstrak tahun dari tanggal mulai kegiatan
                                            $activityYear = Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan)->year;

                                            // 3. Query mitra yang punya kemitraan aktif di tahun tersebut
                                            return Mitra::whereHas('kemitraans', function ($query) use ($activityYear) {
                                                $query->where('tahun', $activityYear)
                                                    ->where('status', 'AKTIF');
                                            })
                                                ->get()
                                                ->mapWithKeys(fn($mitra) => [$mitra->id => "({$mitra->id_sobat}) {$mitra->nama_1}"])
                                                ->toArray();
                                        })
                                        ->searchable()
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
                        // ... Logika action tidak berubah ...
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

                                $tanggalAkhirKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_akhir_pelaksanaan);
                                $tanggalMulaiKegiatan = Carbon::parse($honor->kegiatanManmit->tgl_mulai_pelaksanaan);

                                $tanggalPengajuanBast = \App\Supports\TanggalMerah::getNextWorkDay($tanggalAkhirKegiatan, -1);
                                $tanggalPengajuanSpk = \App\Supports\TanggalMerah::getNextWorkDay($tanggalMulaiKegiatan);

                                $existingSpkId = \App\Models\AlokasiHonor::where('mitra_id', $mitraId)
                                    ->whereHas('honor.kegiatanManmit', function ($query) use ($tanggalMulaiKegiatan) {
                                        $query->whereYear('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->year)
                                            ->whereMonth('tgl_mulai_pelaksanaan', $tanggalMulaiKegiatan->month);
                                    })
                                    ->whereNotNull('surat_perjanjian_kerja_id')
                                    ->value('surat_perjanjian_kerja_id');

                                if ($existingSpkId) {
                                    $suratPerjanjianKerjaId = $existingSpkId;
                                } else {
                                    $nomorSuratSpk = \App\Models\NomorSurat::generateNomorSuratPerjanjianKerja($tanggalPengajuanSpk);
                                    $suratPerjanjianKerjaId = $nomorSuratSpk->id;
                                }
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
                                $totalHonor = $honor->harga_per_satuan * $alokasi['target_per_satuan_honor'];

                                $livewire->ownerRecord->alokasiHonors()->create([
                                    'honor_id' => $data['honor_id'],
                                    'mitra_id' => $mitraId,
                                    'target_per_satuan_honor' => $alokasi['target_per_satuan_honor'],
                                    'total_honor' => $totalHonor,
                                    'surat_perjanjian_kerja_id' => $suratPerjanjianKerjaId,
                                    'surat_bast_id' => $suratBastId,
                                    'tanggal_penanda_tanganan_spk_oleh_petugas' => $tanggalPengajuanSpk,
                                    'tanggal_mulai_perjanjian' => $tanggalMulaiKegiatan,
                                    'tanggal_akhir_perjanjian' => $tanggalAkhirKegiatan,
                                ]);

                                $createdCount++;
                            }

                            if ($createdCount > 0) {
                                Notification::make()->title('Alokasi Berhasil')->body("Berhasil membuat {$createdCount} alokasi honor.")->success()->send();
                            } else {
                                Notification::make()->title('Tidak Ada Data Valid')->body('Tidak ada alokasi yang berhasil dibuat.')->warning()->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()->title('Error')->body('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
                        }
                    })
                    ->modalWidth('5xl'),
                Action::make('honor_baru')
                    ->label("Buat Honor Baru")
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(route("filament.a.resources.honors.create")),
                ActionGroup::make([
                    // Tombol untuk Cetak Kontrak
                    Action::make('cetakKontrak')
                        ->label('Cetak Kontrak')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(function (RelationManager $livewire): string {
                            $kegiatanManmit = $livewire->ownerRecord;
                            $tanggalMulai = Carbon::parse($kegiatanManmit->tgl_mulai_pelaksanaan);
                            $tahun = $tanggalMulai->year;
                            $bulan = $tanggalMulai->month;

                            // TAMBAHKAN 'id_kegiatan_manmit' ke parameter route
                            return route('cetak.kontrak', [
                                'tahun' => $tahun,
                                'bulan' => $bulan,
                                'id_kegiatan_manmit' => $kegiatanManmit->id
                            ]);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn(RelationManager $livewire) => !is_null($livewire->ownerRecord->tgl_mulai_pelaksanaan)),

                    // --- PERUBAHAN PADA TOMBOL CETAK BAST ---
                    Action::make('cetakBast')
                        ->label('Cetak BAST')
                        ->icon('heroicon-o-document-check')
                        ->color('success')
                        ->url(function (RelationManager $livewire): string {
                            $kegiatanManmit = $livewire->ownerRecord;
                            $tanggalAkhir = Carbon::parse($kegiatanManmit->tgl_akhir_pelaksanaan);
                            $tahun = $tanggalAkhir->year;
                            $bulan = $tanggalAkhir->month;

                            // TAMBAHKAN 'id_kegiatan_manmit' ke parameter route
                            return route('cetak.bast', [
                                'tahun' => $tahun,
                                'bulan' => $bulan,
                                'id_kegiatan_manmit' => $kegiatanManmit->id
                            ]);
                        })
                        ->openUrlInNewTab()
                        ->visible(fn(RelationManager $livewire) => !is_null($livewire->ownerRecord->tgl_akhir_pelaksanaan)),

                ])->label('Cetak Dokumen')->button()->color('success'),
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
