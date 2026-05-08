<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanManmitResource\Pages;
use App\Filament\Resources\KegiatanManmitResource\RelationManagers\AlokasiHonorRelationManager;
use App\Models\KegiatanManmit;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Str;

class KegiatanManmitResource extends Resource
{
    protected static ?string $model = KegiatanManmit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $slug = 'kegiatan-manmit';

    protected static ?string $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 1;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Kegiatan Utama')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->helperText('Contoh: (VHTS25) Survei Hotel Bulanan 2025'),

                        Forms\Components\Select::make('jenis_kegiatan')
                            ->options([
                                'SENSUS' => 'SENSUS',
                                'SURVEI' => 'SURVEI',
                            ])
                            ->required()
                            ->live()
                            ->default('SURVEI'),

                        Forms\Components\Select::make('frekuensi_kegiatan')
                            ->options(Constants::FREKUENSI_KEGIATAN_OPTIONS)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Reset periods when frequency changes
                                $options = [];
                                switch ($state) {
                                    case Constants::FREKUENSI_TRIWULANAN:
                                        $options = Constants::TRIWULAN_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_BULANAN:
                                        $options = Constants::BULAN_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_SEMESTERAN:
                                        $options = Constants::SEMESTER_OPTIONS;
                                        break;
                                    case Constants::FREKUENSI_SUBROUND:
                                        $options = Constants::SUBROUND_OPTIONS;
                                        break;
                                }

                                $items = [];
                                foreach ($options as $key => $name) {
                                    $items[] = [
                                        'period_key' => $key,
                                        'period_name' => $name,
                                        'tgl_mulai' => null,
                                        'tgl_akhir' => null
                                    ];
                                }
                                $set('periods', $items);
                            }),
                    ]),

                Forms\Components\Section::make('Template Kontrak Sensus')
                    ->description('Isi bagian ini jika kegiatan adalah SENSUS dan membutuhkan pasal-pasal khusus.')
                    ->visible(fn(Get $get) => $get('jenis_kegiatan') === 'SENSUS')
                    ->schema([
                        Forms\Components\Placeholder::make('helper_placeholders')
                            ->label('Panduan Kustomisasi Kontrak')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div class="space-y-4">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800 text-sm">
                                        <b>Info Layout:</b> 
                                        <ul class="list-disc ml-4 mt-1 space-y-1">
                                            <li>Editor ini hanya mengganti bagian <u>Pasal-Pasal Kontrak</u>.</li>
                                            <li>Halaman pertama setiap bagian (Kontrak/Lampiran) <b>tidak memiliki nomor halaman</b>.</li>
                                            <li>Nomor halaman otomatis muncul di <b>Tengah Atas</b> mulai halaman ke-2.</li>
                                            <li>Sistem secara otomatis mendeteksi lebar kertas (Tegak/Mendatar) untuk memastikan posisi nomor halaman tetap presisi di tengah.</li>
                                            <li><b>Lampiran 1</b> (Daftar Kegiatan & Honor) akan otomatis ditambahkan oleh sistem di lembar terpisah dengan format <b>Landscape</b> dan nomor halaman yang di-reset.</li>
                                        </ul>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm border p-3 rounded-lg bg-gray-50 dark:bg-gray-800 dark:border-gray-700 font-mono">
                                        <div><span class="text-primary-600 font-bold">[NAMA_MITRA]</span>: Nama Lengkap</div>
                                        <div><span class="text-primary-600 font-bold">[NIK_MITRA]</span>: NIK Mitra</div>
                                        <div><span class="text-primary-600 font-bold">[ALAMAT_MITRA]</span>: Alamat Detail</div>
                                        <div><span class="text-primary-600 font-bold">[NAMA_PPK]</span>: Nama PPK</div>
                                        <div><span class="text-primary-600 font-bold">[NIP_PPK]</span>: NIP PPK</div>
                                        <div><span class="text-primary-600 font-bold">[NAMA_KEGIATAN]</span>: Nama Kegiatan</div>
                                        <div><span class="text-primary-600 font-bold">[TOTAL_HONOR]</span>: Total Rp. Honor</div>
                                        <div><span class="text-primary-600 font-bold">[TANGGAL_MULAI]</span>: Tgl Mulai</div>
                                        <div><span class="text-primary-600 font-bold">[TANGGAL_SELESAI]</span>: Tgl Selesai</div>
                                        <div><span class="text-primary-600 font-bold">[NOMOR_SURAT]</span>: Nomor SPK</div>
                                    </div>

                                    <div class="bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg border border-amber-200 dark:border-amber-800 text-sm">
                                        <b>Tips Pindah Halaman & Orientasi:</b> Jika ingin pindah halaman atau mengubah arah kertas manual, klik tombol <b>"Source Code" (&lt;&gt;)</b> di editor dan masukkan: <br>
                                        <ul class="list-disc ml-4 mt-1 font-mono text-xs space-y-1">
                                            <li><code class="bg-white dark:bg-gray-900 px-1 py-0.5 rounded border">&lt;div class="page-break"&gt;&lt;/div&gt;</code> (Pindah Halaman Saja)</li>
                                            <li><code class="bg-white dark:bg-gray-900 px-1 py-0.5 rounded border">&lt;div class="lanskap"&gt;&lt;/div&gt;</code> (Pindah Halaman + Jadi Lanskap)</li>
                                            <li><code class="bg-white dark:bg-gray-900 px-1 py-0.5 rounded border">&lt;div class="potret"&gt;&lt;/div&gt;</code> (Pindah Halaman + Jadi Potret)</li>
                                        </ul>
                                    </div>
                                </div>
                            ')),
                        Forms\Components\RichEditor::make('template_kontrak')
                            ->label('Isi Pasal-Pasal Kontrak')
                            ->helperText('Gunakan editor ini untuk menentukan isi Pasal 1, Pasal 2, dst secara spesifik. Jika kosong, akan menggunakan template default.')
                            ->columnSpanFull(),
                    ]),

                // --- REPEATER DINAMIS YANG SUDAH DIPERBAIKI ---
                Repeater::make('periods')
                    ->label('Jadwal Periode Kegiatan')
                    ->schema([
                        Forms\Components\Hidden::make('period_key'),
                        Forms\Components\TextInput::make('period_name')
                            ->label('Nama Periode')
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_mulai')
                            ->date()
                            ->label('Tanggal Mulai')
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_akhir')
                            ->date()
                            ->label('Tanggal Selesai')
                            ->required(),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(true)
                    ->reorderable(false)
                    ->visible(fn(Get $get) => !in_array($get('frekuensi_kegiatan'), Constants::SINGLE_OCCURRENCE_FREQUENCIES))
                    ->default([]), // Start with empty array

                // --- BAGIAN UNTUK FREKUENSI TUNGGAL ---
                Forms\Components\Section::make('Jadwal Kegiatan')
                    ->schema([
                        Forms\Components\DatePicker::make('tgl_mulai_pelaksanaan')
                            ->date()
                            ->required(),
                        Forms\Components\DatePicker::make('tgl_akhir_pelaksanaan')
                            ->date()
                            ->required(),
                    ])
                    ->columns(2)
                    ->visible(fn(Get $get) => in_array($get('frekuensi_kegiatan'), Constants::SINGLE_OCCURRENCE_FREQUENCIES)),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort("updated_at", "desc")
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_kegiatan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        Constants::SENSUS => 'danger',
                        Constants::SURVEI => 'success',
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('frekuensi_kegiatan')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tgl_mulai_pelaksanaan')
                    ->label('Mulai Pelaksanaan')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tgl_akhir_pelaksanaan')
                    ->label('Akhir Pelaksanaan')
                    ->dateTime('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AlokasiHonorRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatanManmits::route('/'),
            'create' => Pages\CreateKegiatanManmit::route('/create'),
            'edit' => Pages\EditKegiatanManmit::route('/{record}/edit'),
        ];
    }
}
