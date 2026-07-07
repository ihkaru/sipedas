<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HonorResource\Pages;
use App\Models\Honor;
use App\Supports\Constants;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// ... (use statements lain)
use Filament\Tables\Actions\Action; // Ganti ImportAction dengan Action
use Filament\Forms\Components\FileUpload;
use Maatwebsite\Excel\Facades\Excel; // Import facade Excel
use App\Imports\HonorImport; // Import kelas importer kita
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification; // Untuk notifikasi
use Filament\Tables\Columns\TextColumn;

class HonorResource extends Resource
{
    protected static ?string $model = Honor::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = "Honor Mitra";
    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detail Honor')
                    ->schema([
                        Select::make('kegiatan_manmit_id')
                            ->relationship('kegiatanManmit', 'nama')
                            ->getOptionLabelFromRecordUsing(fn($record) => "({$record->id}) {$record->nama}")
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // Agar ID Batasan Honor terupdate
                            ->label('Kegiatan Manmit'),

                        Select::make('jabatan')
                            ->options(Constants::JABATAN_MITRA_OPTIONS)
                            ->live() // Agar ID Batasan Honor terupdate
                            ->required(),

                        Select::make('jenis_honor')
                            ->options(Constants::JENIS_HONOR_OPTIONS)
                            ->required(),

                        Select::make('satuan_honor')
                            ->options(Constants::SATUAN_HONOR_OPTIONS)
                            ->required()
                            ->label('Satuan Honor'),

                        TextInput::make('harga_per_satuan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp'),

                        DatePicker::make('tanggal_akhir_kegiatan')
                            ->required()
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->live()
                            ->rules([
                                function (Forms\Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $kegiatanId = $get('kegiatan_manmit_id');
                                        if (!$kegiatanId || !$value) return;

                                        $kegiatan = \App\Models\KegiatanManmit::find($kegiatanId);
                                        if (!$kegiatan || !$kegiatan->tgl_mulai_pelaksanaan || !$kegiatan->tgl_akhir_pelaksanaan) return;

                                        $start = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                                        $end = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);
                                        $chosen = \Carbon\Carbon::parse($value);

                                        if ($chosen->lt($start) || $chosen->gt($end)) {
                                            $tglMulai = $start->format('d M Y');
                                            $tglAkhir = $end->format('d M Y');
                                            $fail("Tanggal akhir kegiatan harus berada dalam rentang pelaksanaan kegiatan utama ({$tglMulai} s/d {$tglAkhir})!");
                                        }
                                    };
                                }
                            ])
                            ->helperText(function (Forms\Get $get, ?Honor $record) {
                                $kegiatanId = $get('kegiatan_manmit_id');
                                $parentRangeText = '';
                                
                                if ($kegiatanId) {
                                    $kegiatan = \App\Models\KegiatanManmit::find($kegiatanId);
                                    if ($kegiatan && $kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
                                        $tglMulai = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan)->format('d M Y');
                                        $tglAkhir = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan)->format('d M Y');
                                        $val = $get('tanggal_akhir_kegiatan');
                                        
                                        if ($val) {
                                            $chosenDate = \Carbon\Carbon::parse($val);
                                            $start = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                                            $end = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);
                                            
                                            if ($chosenDate->lt($start) || $chosenDate->gt($end)) {
                                                $editUrl = route('filament.a.resources.kegiatan-manmit.edit', ['record' => $kegiatanId]);
                                                $parentRangeText = "<div class='text-danger-600 dark:text-danger-400 font-semibold mt-2'>❌ Error: Tanggal yang dipilih berada di luar rentang pelaksanaan kegiatan ({$tglMulai} s/d {$tglAkhir}).</div>";
                                                $parentRangeText .= "<div class='mt-2'><a href='{$editUrl}' target='_blank' class='inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-danger-600 hover:bg-danger-500 rounded-lg shadow-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger-600'>Ubah Rentang Tanggal Pelaksanaan &rarr;</a></div>";
                                            } else {
                                                $parentRangeText = "<div class='text-green-600 dark:text-green-400 mt-2'>✓ Tanggal berada dalam rentang pelaksanaan kegiatan ({$tglMulai} s/d {$tglAkhir}).</div>";
                                            }
                                        } else {
                                            $parentRangeText = "<div class='text-gray-500 mt-2'>Rentang pelaksanaan kegiatan: {$tglMulai} s/d {$tglAkhir}.</div>";
                                        }
                                    }
                                }

                                $baseText = '';
                                if (!$record) {
                                    $baseText = 'Tanggal ini menentukan bulan kontrak SPK, PPK penandatangan, dan tanggal BAST.';
                                } else {
                                    $jumlah = $record->alokasiHonors()->count();
                                    if ($jumlah === 0) {
                                        $baseText = 'Belum ada alokasi honor — aman untuk diubah.';
                                    } else {
                                        $baseText = "⚠️ Ada {$jumlah} alokasi honor aktif. Jika diubah, sistem akan otomatis memperbarui tanggal kontrak, penanda tanganan SPK, dan nomor BAST seluruh mitra terdampak.";
                                    }
                                }

                                return new \Illuminate\Support\HtmlString($baseText . $parentRangeText);
                             })
                            ->hintColor(function (Forms\Get $get, ?Honor $record) {
                                $kegiatanId = $get('kegiatan_manmit_id');
                                if ($kegiatanId) {
                                    $kegiatan = \App\Models\KegiatanManmit::find($kegiatanId);
                                    if ($kegiatan && $kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
                                        $val = $get('tanggal_akhir_kegiatan');
                                        if ($val) {
                                            $chosenDate = \Carbon\Carbon::parse($val);
                                            $start = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                                            $end = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);
                                            if ($chosenDate->lt($start) || $chosenDate->gt($end)) {
                                                return 'danger';
                                            }
                                        }
                                    }
                                }
                                if (!$record) return 'primary';
                                return $record->alokasiHonors()->count() > 0 ? 'warning' : 'success';
                            })
                            ->hintIcon(function (Forms\Get $get, ?Honor $record) {
                                $kegiatanId = $get('kegiatan_manmit_id');
                                if ($kegiatanId) {
                                    $kegiatan = \App\Models\KegiatanManmit::find($kegiatanId);
                                    if ($kegiatan && $kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
                                        $val = $get('tanggal_akhir_kegiatan');
                                        if ($val) {
                                            $chosenDate = \Carbon\Carbon::parse($val);
                                            $start = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                                            $end = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);
                                            if ($chosenDate->lt($start) || $chosenDate->gt($end)) {
                                                return 'heroicon-o-x-circle';
                                            }
                                        }
                                    }
                                }
                                if (!$record) return null;
                                return $record->alokasiHonors()->count() > 0
                                    ? 'heroicon-o-exclamation-triangle'
                                    : 'heroicon-o-check-circle';
                            })
                            ->hint(function (Forms\Get $get, ?Honor $record) {
                                $kegiatanId = $get('kegiatan_manmit_id');
                                if ($kegiatanId) {
                                    $kegiatan = \App\Models\KegiatanManmit::find($kegiatanId);
                                    if ($kegiatan && $kegiatan->tgl_mulai_pelaksanaan && $kegiatan->tgl_akhir_pelaksanaan) {
                                        $val = $get('tanggal_akhir_kegiatan');
                                        if ($val) {
                                            $chosenDate = \Carbon\Carbon::parse($val);
                                            $start = \Carbon\Carbon::parse($kegiatan->tgl_mulai_pelaksanaan);
                                            $end = \Carbon\Carbon::parse($kegiatan->tgl_akhir_pelaksanaan);
                                            if ($chosenDate->lt($start) || $chosenDate->gt($end)) {
                                                return 'Di luar rentang pelaksanaan!';
                                            }
                                        }
                                    }
                                }
                                if (!$record) return null;
                                $jumlah = $record->alokasiHonors()->count();
                                return $jumlah > 0 ? "{$jumlah} alokasi akan ikut terupdate" : 'Aman diubah';
                            }),

                        // Menampilkan ID Batasan Honor sebagai informasi (tidak bisa di-edit)
                        Placeholder::make('id_batasan_honor')
                            ->label('ID Batasan Honor')
                            ->content(function (Forms\Get $get, ?Honor $record): string {
                                if ($record) {
                                    return $record->id_batasan_honor;
                                }
                                $kegiatan = \App\Models\KegiatanManmit::find($get('kegiatan_manmit_id'));
                                if ($kegiatan && $get('jabatan')) {
                                    return $kegiatan->jenis_kegiatan . '-' . $get('jabatan');
                                }
                                return 'Akan dibuat setelah form diisi';
                            }),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('kegiatanManmit.nama')->label('Nama Kegiatan')->searchable()->sortable()->wrap(),
                TextColumn::make('jabatan')->searchable()->badge(),
                TextColumn::make('id_batasan_honor')->label('ID Batasan'),
                TextColumn::make('harga_per_satuan')->money('IDR')->sortable(),
                TextColumn::make('tanggal_akhir_kegiatan')->date('d M Y')->sortable()->label('Akhir Kegiatan'),
                TextColumn::make('tanggal_pembayaran_maksimal')->date('d M Y')->sortable()->label('Batas Bayar'),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHonors::route('/'),
            'create' => Pages\CreateHonor::route('/create'),
            'edit' => Pages\EditHonor::route('/{record}/edit'),
        ];
    }
}
