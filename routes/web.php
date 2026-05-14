<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Map Routes
    Route::get('/map', [App\Http\Controllers\MapController::class, 'index'])->name('map');
    Route::post('/location/ping', [App\Http\Controllers\AgencyLocationController::class, 'update'])->name('location.ping');

    // Agency Resource Management (Livewire)
    Route::get('/resources', fn () => view('resources.index'))->name('resources.index');

    // Secure Messaging (Livewire)
    Route::get('/chat', fn () => view('chat.index'))->name('chat.index');
    Route::get('/chat/{agency}', fn (\App\Models\Agency $agency) => view('chat.index', compact('agency')))->name('chat.with');

    // Alert Routes
    Route::get('/alerts', [App\Http\Controllers\AlertController::class, 'index'])->name('alerts.index');
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/alerts/create', [App\Http\Controllers\AlertController::class, 'create'])->name('alerts.create');
        Route::post('/alerts', [App\Http\Controllers\AlertController::class, 'store'])->name('alerts.store');
        Route::patch('/alerts/{alert}/deactivate', [App\Http\Controllers\AlertController::class, 'deactivate'])->name('alerts.deactivate');
    });
});

// Agency Registration Flow (Publicly accessible form)
Route::get('/register-agency', [App\Http\Controllers\AgencyRegistrationController::class, 'create'])
    ->name('agency.register');
Route::post('/register-agency', [App\Http\Controllers\AgencyRegistrationController::class, 'store']);

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/agencies', [App\Http\Controllers\Admin\AgencyApprovalController::class, 'index'])->name('agencies.index');
    Route::post('/agencies/{agency}/approve', [App\Http\Controllers\Admin\AgencyApprovalController::class, 'approve'])->name('agencies.approve');

    // Reports & Dashboard
    Route::get('/reports',        [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/csv',    [App\Http\Controllers\Admin\ReportController::class, 'exportCsv'])->name('reports.csv');
    Route::get('/reports/pdf',    [App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('reports.pdf');
});

require __DIR__.'/auth.php';
