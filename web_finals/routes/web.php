<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\RTLController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Renstra routes - accessible by admin, bpap
Route::middleware(['auth', 'role:admin,bpap'])->group(function () {
    Route::resource('renstra', RenstraController::class)->except(['index', 'show']);
});

// Renstra view routes - accessible by all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/renstra', [RenstraController::class, 'index'])->name('renstra.index');
    Route::get('/renstra/{renstra}', [RenstraController::class, 'show'])->name('renstra.show');
});

// Evaluasi routes - accessible by admin, kaprodi, gpm, dekan
Route::middleware(['auth', 'role:admin,kaprodi,gpm,dekan'])->group(function () {
    Route::resource('evaluasi', EvaluasiController::class)->except(['index', 'show']);
    Route::post('/evaluasi/{evaluasi}/approve', [EvaluasiController::class, 'approve'])->name('evaluasi.approve');
    Route::post('/evaluasi/{evaluasi}/reject', [EvaluasiController::class, 'reject'])->name('evaluasi.reject');
});

// Evaluasi view routes - accessible by all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/evaluasi', [EvaluasiController::class, 'index'])->name('evaluasi.index');
    Route::get('/evaluasi/{evaluasi}', [EvaluasiController::class, 'show'])->name('evaluasi.show');
});

// RTL routes - accessible by admin, gkm
Route::middleware(['auth', 'role:admin,gkm'])->group(function () {
    Route::resource('rtl', RTLController::class)->except(['index', 'show']);
    Route::post('/rtl/{rtl}/start-progress', [RTLController::class, 'startProgress'])->name('rtl.start-progress');
    Route::post('/rtl/{rtl}/complete', [RTLController::class, 'complete'])->name('rtl.complete');
});

// RTL verify - accessible by admin, gpm, dekan
Route::middleware(['auth', 'role:admin,gpm,dekan'])->group(function () {
    Route::post('/rtl/{rtl}/verify', [RTLController::class, 'verify'])->name('rtl.verify');
});

// RTL view routes - accessible by all authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/rtl', [RTLController::class, 'index'])->name('rtl.index');
    Route::get('/rtl/{rtl}', [RTLController::class, 'show'])->name('rtl.show');
});

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
