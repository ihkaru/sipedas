<?php

use App\Http\Controllers\ArtisanController;
use Illuminate\Support\Facades\Route;

Route::get("/run",[ArtisanController::class,"run"]);
Route::get('/', function () {
    return view('welcome');
});
