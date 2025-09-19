<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SpotifyController;
use App\Http\Controllers\PlaylistExportController;
use App\Http\Controllers\DeleteExportController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/playlists/export', [PlaylistExportController::class, 'store'])->name('playlists.export');
    Route::delete('/exports/{export}', [DeleteExportController::class, '__invoke'])->middleware(['auth', 'verified'])->name('playlists.delete');
    Route::get('/exports/{export}/download', [\App\Http\Controllers\DownloadExportController::class, '__invoke'])->middleware(['auth', 'verified'])->name('playlists.download');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::post('/admin/approve/{user}', [AdminController::class, 'approveUser'])->name('admin.approve');
});

Route::post('/spotify/connect', [SpotifyController::class, 'connect'])->name('spotify.connect');
Route::get('/spotify/callback', [SpotifyController::class, 'callback'])->name('spotify.callback');

require __DIR__.'/auth.php';
