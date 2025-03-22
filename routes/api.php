<?php

use App\Http\Controllers\Api\MicrositeController;
use Illuminate\Support\Facades\Route;

Route::get('/microsites/{slug}', [MicrositeController::class, 'show']);
