<?php

use App\Http\Controllers\ArtisanController;
use App\Http\Controllers\PdfController;
use App\Providers\Filament\APanelProvider;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Support\Facades\Route;

Route::get("/run",[ArtisanController::class,"run"]);
Route::get('/', function () {
    return view('welcome');
});
Route::get('/cetak/penugasan/{id}', [PdfController::class,'cetakPenugasan'])->name("cetak.penugasan");
Route::get('/cetak/penugasan-bersama/{id}', [PdfController::class,'cetakPenugasanBersama'])->name("cetak.penugasan-bersama");
Route::get('/cetak/kontrak', [PdfController::class,'cetakKontrak'])->name("cetak.kontrak");
Route::get('/cetak/bast', [PdfController::class,'cetakBast'])->name("cetak.bast");
