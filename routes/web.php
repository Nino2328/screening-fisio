<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AthleteController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MentalController;
use App\Http\Controllers\TaktikController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
    Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    // Password reset (Laravel built-in)
    Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (requires login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Root redirect
    Route::get('/', fn() => redirect()->route('dashboard.index'));

    /* ---- Dashboard ---- */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    /* ---- Athletes ---- */
    // Semua role auth bisa lihat daftar & detail
    Route::get('/athletes', [AthleteController::class, 'index'])->name('athletes.index');
    Route::get('/athletes/{id}', [AthleteController::class, 'show'])->name('athletes.show');

    // Hanya admin & fisio yang boleh tambah/edit/hapus
    Route::post('/athletes', [AthleteController::class, 'store'])->name('athletes.store')->middleware('role:fisio');
    Route::put('/athletes/{id}', [AthleteController::class, 'update'])->name('athletes.update')->middleware('role:fisio');
    Route::delete('/athletes/{id}', [AthleteController::class, 'destroy'])->name('athletes.destroy')->middleware('role:fisio');

    /* ---- SCREENING FISIOTERAPI FORMS & ROUTES (Fisio & Admin) ---- */
    Route::get('/tests', [TestController::class, 'index'])->name('tests.index')->middleware('role:fisio');

    // Forms routes
    $forms = [
        'endurance',
        'speed',
        'agility',
        'strength',
        'power',
        'accuracy',
        'reaction',
        'coordination',
        'flexibility',
        'balance',
    ];

    foreach ($forms as $form) {
        Route::get("/forms/{$form}", [TestController::class, "form_" . $form])
            ->name("forms.{$form}")->middleware('role:fisio');
        Route::post("/forms/{$form}", [TestController::class, "store_" . $form])
            ->name("forms.{$form}.store")->middleware('role:fisio');
    }

    /* ---- REPORT FISIOTERAPI (Fisio & Admin) ---- */
    Route::prefix('report')->middleware('role:fisio')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report.index');
        Route::get('/export/pdf', [ReportController::class, 'exportPdf'])->name('report.export.pdf');
        Route::get('/export/excel', [ReportController::class, 'exportExcel'])->name('report.export.excel');
        Route::get('/athlete/{id}', [ReportController::class, 'athleteReport'])->name('report.athlete');
    });

    /* ---- MENTAL ASSESSMENT (Pelatih & Admin) ---- */
    Route::middleware('role:pelatih')->group(function () {
        Route::resource('mental', MentalController::class);
    });

    /* ---- TAKTIK ASSESSMENT (Pelatih & Admin) ---- */
    Route::middleware('role:pelatih')->group(function () {
        Route::resource('taktik', TaktikController::class);
    });

    /* ---- REPORTS PELATIH (Pelatih & Admin) ---- */
    Route::prefix('reports')->middleware('role:pelatih')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/export/pdf', [ReportsController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/export/excel', [ReportsController::class, 'exportExcel'])->name('reports.export.excel');
    });
});
