<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\HonorTemplateController;
use App\Http\Controllers\MitraTemplateController;
use App\Http\Controllers\PdfController;
use App\Providers\Filament\APanelProvider;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get("/run", [ArtisanController::class, "run"]);
Route::get('/clear-all-cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('filament:clear-cached-components');
    // Artisan::call('filament:optimize');
    return "All caches cleared and then optimized!";
});
Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {
    return redirect('/a/login');
})->name('login');
Route::get('/cetak/penugasan/{id}', [PdfController::class, 'cetakPenugasan'])->name("cetak.penugasan");
Route::get('/cetak/penugasan-bersama/{id}', [PdfController::class, 'cetakPenugasanBersama'])->name("cetak.penugasan-bersama");
Route::get('/cetak/kontrak', [PdfController::class, 'cetakKontrak'])->name("cetak.kontrak");
Route::get('/cetak/bast', [PdfController::class, 'cetakBast'])->name("cetak.bast");
Route::get('/approve/{token}', [ApprovalController::class, 'handleApproval'])->name('one-click.approve');


Route::middleware('auth')->group(function () {
    Route::get('/download/template-kegiatan-manmit', [ExcelExportController::class, 'downloadKegiatanTemplate'])
        ->name('download.template.kegiatan-manmit');
    Route::get('/download/template/honor', [HonorTemplateController::class, 'download'])
        ->name('download.template.honor');
    Route::get('/download/template/mitra', [MitraTemplateController::class, 'download'])
        ->name('download.template.mitra');
    Route::get('/download/template/alokasi-honor', [ExcelExportController::class, 'downloadAlokasiHonorTemplate'])
        ->name('download.template.alokasi-honor');
});
