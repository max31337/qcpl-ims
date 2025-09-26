<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;

Route::view('/', 'welcome')->name('welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin - User Management (Livewire component)
Route::get('/admin/invitations', UserManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.invitations');

// Assets Management Routes
Route::middleware(['auth', 'verified'])->prefix('assets')->name('assets.')->group(function () {
    Route::get('/', \App\Livewire\Assets\AssetList::class)->name('index');
    Route::get('/create', \App\Livewire\Assets\AssetForm::class)->name('form');
    Route::get('/{assetId}/edit', \App\Livewire\Assets\AssetForm::class)->name('edit');
    Route::get('/{assetId}/transfer', \App\Livewire\Assets\AssetTransfer::class)->name('transfer');
    Route::get('/{assetId}/history', \App\Livewire\Assets\AssetHistory::class)->name('history');
    Route::get('/reports', \App\Livewire\Assets\AssetReports::class)->name('reports');
});

// Load Breeze/Laravel auth routes (registers logout route)
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
