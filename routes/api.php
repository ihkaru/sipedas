<?php

use App\Http\Controllers\Api\AlokasiHonorApiController;
use App\Http\Controllers\Api\AuditLogApiController;
use App\Http\Controllers\Api\DokumenApiController;
use App\Http\Controllers\Api\KegiatanManmitApiController;
use App\Http\Controllers\Api\MicrositeController;
use App\Http\Controllers\Api\MitraApiController;
use App\Http\Controllers\Api\SkillApiController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/microsites/{slug}', [MicrositeController::class, 'show']);

// Agent Skill Specification endpoint (Bisa diakses publik atau dengan key)
Route::get('/v1/skill', [SkillApiController::class, 'show']);
Route::get('/v1/skill.md', [SkillApiController::class, 'show']);

// Protected REST API routes for Coding Agents & Integrations (Requires API Key)
Route::prefix('v1')->middleware('api.key')->group(function () {
    // 1. Discovery & Management Endpoints (Lookup Kegiatan, Honor, Mitra, Migration)
    Route::get('/kegiatan-manmit', [KegiatanManmitApiController::class, 'index']);
    Route::post('/kegiatan-manmit/{id}/rename-id', [KegiatanManmitApiController::class, 'renameId']);
    Route::get('/mitras', [MitraApiController::class, 'index']);
    Route::get('/mitras/{id}', [MitraApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/mitras/{id}', [MitraApiController::class, 'update']);

    // 2. Alokasi Honor & Automatic SPK/BAST Trigger
    Route::post('/alokasi/check', [AlokasiHonorApiController::class, 'check']);
    Route::get('/alokasi', [AlokasiHonorApiController::class, 'index']);
    Route::post('/alokasi', [AlokasiHonorApiController::class, 'store']);
    Route::get('/alokasi/{id}', [AlokasiHonorApiController::class, 'show']);
    Route::delete('/alokasi/{id}', [AlokasiHonorApiController::class, 'destroy']);

    // 3. Dokumen Kontrak & BAST Query Endpoints
    Route::get('/kontrak', [DokumenApiController::class, 'kontrak']);
    Route::get('/bast', [DokumenApiController::class, 'bast']);

    // 4. Audit Log & Rollback Endpoints
    Route::get('/audit-logs', [AuditLogApiController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogApiController::class, 'show']);
    Route::post('/audit-logs/{id}/rollback', [AuditLogApiController::class, 'rollback']);
});
