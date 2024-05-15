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
Route::get('/pdf/{id}', [PdfController::class,'cetak'])->name("cetak");
