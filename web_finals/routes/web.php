<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\RTLController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\TargetController;
use Illuminate\Support\Facades\Route;


/*Route::get('/', function () {
    return view('welcome');
});*/

// Dashboard
Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =========================================================================
// 1. MASTER DATA RENSTRA (WAJIB DITARUH PALING ATAS)
// =========================================================================
// Penjelasan: Ini harus ditaruh SEBELUM route 'renstra/{id}' agar URL 
// seperti '/renstra/kategori' tidak dianggap sebagai ID Renstra.
Route::prefix('renstra')->name('renstra.')->middleware(['auth'])->group(function () {
    Route::resource('kategori', KategoriController::class)->except(['show', 'edit', 'update']);
    Route::resource('kegiatan', KegiatanController::class)->except(['show', 'edit', 'update']);
    Route::resource('indikator', IndikatorController::class)->except(['show', 'edit', 'update']);
    Route::resource('target', TargetController::class)->except(['show', 'edit', 'update']);
});

// =========================================================================
// 2. RENSTRA UTAMA
// =========================================================================

// Renstra actions (Create, Store, Edit, Update, Destroy) - Admin/BPAP only
Route::middleware(['auth', 'role:admin,bpap'])->group(function () {
    Route::resource('renstra', RenstraController::class)->except(['index', 'show']);
});

// Renstra view (Index, Show) - All Auth Users
Route::middleware(['auth'])->group(function () {
    Route::get('/renstra', [RenstraController::class, 'index'])->name('renstra.index');
    Route::get('/renstra/{renstra}', [RenstraController::class, 'show'])->name('renstra.show');
});

// =========================================================================
// 3. EVALUASI
// =========================================================================

Route::middleware(['auth'])->group(function () {
    // Custom Actions (Submit, Verify, Approve, Reject)
    // Didefinisikan DULUAN sebelum resource agar tidak tertimpa
    Route::post('/evaluasi/{evaluasi}/submit', [EvaluasiController::class, 'submit'])->name('evaluasi.submit');
    Route::patch('/evaluasi/{evaluasi}/verify', [EvaluasiController::class, 'verify'])->name('evaluasi.verify');
    Route::patch('/evaluasi/{evaluasi}/approve', [EvaluasiController::class, 'approve'])->name('evaluasi.approve');
    Route::patch('/evaluasi/{evaluasi}/reject', [EvaluasiController::class, 'reject'])->name('evaluasi.reject');

    // View & Standard Resource
    Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('/evaluasi/{evaluasi}', [EvaluasiController::class, 'show'])->name('evaluasi.show');
    
    // Create/Store/Edit/Update/Destroy dibatasi Role
    Route::middleware(['role:admin,kaprodi,gpm,dekan'])->group(function () {
        Route::resource('evaluasi', EvaluasiController::class)->except(['index', 'show']);
    });
});

// =========================================================================
// 4. RTL (Rencana Tindak Lanjut)
// =========================================================================

Route::middleware(['auth'])->group(function () {
    // Custom RTL Actions
    Route::middleware(['role:admin,gkm'])->group(function () {
        Route::post('/rtl/{rtl}/start-progress', [RTLController::class, 'startProgress'])->name('rtl.start-progress');
        Route::post('/rtl/{rtl}/complete', [RTLController::class, 'complete'])->name('rtl.complete');
        Route::resource('rtl', RTLController::class)->except(['index', 'show']);
    });

    // RTL Verify
    Route::middleware(['role:admin,gpm,dekan'])->group(function () {
        Route::post('/rtl/{rtl}/verify', [RTLController::class, 'verify'])->name('rtl.verify');
    });

    // RTL View
    Route::get('/rtl', [RTLController::class, 'index'])->name('rtl.index');
    Route::get('/rtl/{rtl}', [RTLController::class, 'show'])->name('rtl.show');
});

// =========================================================================
// 5. USER MANAGEMENT & REPORTS
// =========================================================================

// User management - admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class);
});

// Reports - accessible by admin, dekan, gpm
Route::middleware(['auth', 'role:admin,dekan,gpm'])->group(function () {
    Route::get('/reports/renstra', [ReportController::class, 'renstraReport'])->name('reports.renstra');
    Route::get('/reports/renstra/pdf', [ReportController::class, 'exportPdf'])->name('reports.renstra.pdf');
});

require __DIR__.'/auth.php';

// Verification routes - accessible by GPM, dekan
Route::middleware(['auth', 'role:admin,GPM,dekan'])->group(function () {
    Route::get('/verifications', [VerificationController::class, 'index'])->name('verifications.index');
    Route::post('/verifications/{id}/update', [VerificationController::class, 'update'])->name('verifications.update');
});   
require __DIR__.'/auth.php';
