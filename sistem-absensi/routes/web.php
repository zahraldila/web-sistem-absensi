<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/tv/{display_token}', [App\Http\Controllers\DashboardTv\TvDashboardController::class, 'index'])->name('tv.dashboard');
Route::get('/api/tv/{display_token}/stats', [App\Http\Controllers\DashboardTv\TvDashboardController::class, 'getStats'])->name('tv.dashboard.stats');

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
});

// Load additional route groups if present (admin, pegawai, auth)
if (file_exists($path = base_path('routes/admin.php'))) {
    require $path;
}

if (file_exists($path = base_path('routes/pegawai.php'))) {
    require $path;
}

if (file_exists($path = base_path('routes/auth.php'))) {
    require $path;
}

// Tahap 4B: Route log-aktivitas ini menimpa nama 'admin.log-aktivitas' dari admin.php.
// Harus dilindungi auth + privilege di sini karena berada di luar grup middleware admin.php.
Route::get('/admin/log-aktivitas', [\App\Http\Controllers\AuditLogControllers::class, 'webIndex'])
    ->name('admin.log-aktivitas')
    ->middleware(['web', 'auth', 'privilege:lihat_log_aktivitas']);