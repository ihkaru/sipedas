<?php

use App\Http\Controllers\Api\AlokasiHonorApiController;
use App\Http\Controllers\Api\AuditLogApiController;
use App\Http\Controllers\Api\DokumenApiController;
use App\Http\Controllers\Api\HonorApiController;
use App\Http\Controllers\Api\KegiatanManmitApiController;
use App\Http\Controllers\Api\MicrositeController;
use App\Http\Controllers\Api\MitraApiController;
use App\Http\Controllers\Api\PegawaiApiController;
use App\Http\Controllers\Api\PenugasanApiController;
use App\Http\Controllers\Api\SkillApiController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/microsites/{slug}', [MicrositeController::class, 'show']);

// Agent Skill Specification endpoint (Bisa diakses publik atau dengan key)
Route::get('/v1/skill', [SkillApiController::class, 'show']);
Route::get('/v1/skill.md', [SkillApiController::class, 'show']);

// Protected REST API routes for Coding Agents & Integrations (Requires API Key)
Route::prefix('v1')->middleware('api.key')->group(function () {
    // 1. Discovery & Management Endpoints (Lookup Kegiatan, Honor, Mitra, Pegawai, Migration)
    Route::get('/kegiatan-manmit', [KegiatanManmitApiController::class, 'index']);
    Route::post('/kegiatan-manmit', [KegiatanManmitApiController::class, 'store']);
    Route::get('/kegiatan-manmit/{id}', [KegiatanManmitApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/kegiatan-manmit/{id}', [KegiatanManmitApiController::class, 'update']);
    Route::delete('/kegiatan-manmit/{id}', [KegiatanManmitApiController::class, 'destroy']);
    Route::post('/kegiatan-manmit/{id}/rename-id', [KegiatanManmitApiController::class, 'renameId']);

    Route::get('/honors', [HonorApiController::class, 'index']);
    Route::post('/honors', [HonorApiController::class, 'store']);
    Route::get('/honors/{id}', [HonorApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/honors/{id}', [HonorApiController::class, 'update']);
    Route::delete('/honors/{id}', [HonorApiController::class, 'destroy']);

    Route::get('/mitras', [MitraApiController::class, 'index']);
    Route::post('/mitras', [MitraApiController::class, 'store']);
    Route::get('/mitras/{id}', [MitraApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/mitras/{id}', [MitraApiController::class, 'update']);
    Route::delete('/mitras/{id}', [MitraApiController::class, 'destroy']);

    Route::get('/pegawais', [PegawaiApiController::class, 'index']);
    Route::post('/pegawais', [PegawaiApiController::class, 'store']);
    Route::get('/pegawais/{nip}', [PegawaiApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/pegawais/{nip}', [PegawaiApiController::class, 'update']);
    Route::delete('/pegawais/{nip}', [PegawaiApiController::class, 'destroy']);

    // 2. Alokasi Honor & Automatic SPK/BAST Trigger
    Route::post('/alokasi/check', [AlokasiHonorApiController::class, 'check']);
    Route::get('/alokasi', [AlokasiHonorApiController::class, 'index']);
    Route::post('/alokasi', [AlokasiHonorApiController::class, 'store']);
    Route::get('/alokasi/{id}', [AlokasiHonorApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/alokasi/{id}', [AlokasiHonorApiController::class, 'update']);
    Route::delete('/alokasi/{id}', [AlokasiHonorApiController::class, 'destroy']);

    // 3. Dokumen Kontrak & BAST Endpoints
    Route::get('/kontrak', [DokumenApiController::class, 'kontrak']);
    Route::match(['put', 'patch', 'post'], '/kontrak/{id}', [DokumenApiController::class, 'updateKontrak']);
    Route::get('/bast', [DokumenApiController::class, 'bast']);
    Route::match(['put', 'patch', 'post'], '/bast/{id}', [DokumenApiController::class, 'updateBast']);

    // 4. Audit Log & Rollback Endpoints
    Route::get('/audit-logs', [AuditLogApiController::class, 'index']);
    Route::get('/audit-logs/{id}', [AuditLogApiController::class, 'show']);
    Route::post('/audit-logs/{id}/rollback', [AuditLogApiController::class, 'rollback']);

    // 5. Surat Tugas & SPD Endpoints (Agent-Native REST Suite)
    Route::post('/penugasan/check', [PenugasanApiController::class, 'check']);
    Route::get('/penugasan', [PenugasanApiController::class, 'index']);
    Route::post('/penugasan', [PenugasanApiController::class, 'store']);
    Route::get('/penugasan/{id}', [PenugasanApiController::class, 'show']);
    Route::match(['put', 'patch', 'post'], '/penugasan/{id}', [PenugasanApiController::class, 'update']);
    Route::delete('/penugasan/{id}', [PenugasanApiController::class, 'destroy']);
    Route::post('/penugasan/{id}/action', [PenugasanApiController::class, 'action']);
});
