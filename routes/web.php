<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;
// Admin Dashboard (Livewire)
use App\Http\Livewire\Admin\Dashboard as AdminDashboard;

Route::view('/', 'welcome')->name('welcome');

// Admin Dashboard
Route::get('/dashboard', AdminDashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin - User Management (Livewire component)
Route::get('/admin/invitations', UserManagement::class)
    ->middleware(['auth', 'verified'])
    ->name('admin.invitations');

// Admin - Assets Reports
Route::middleware(['auth', 'verified'/*, 'check.role:admin,observer'*/])
    ->get('/admin/assets/reports', \App\Livewire\Assets\AssetReports::class)
    ->name('admin.assets.reports');

// Supplies Management Routes
Route::middleware(['auth', 'verified'])->prefix('supplies')->name('supplies.')->group(function () {
    Route::get('/', \App\Livewire\Supplies\SupplyList::class)->name('index');
    Route::get('/create', \App\Livewire\Supplies\SupplyForm::class)->name('create');
    Route::get('/{id}/edit', \App\Livewire\Supplies\SupplyForm::class)->name('edit');
    Route::get('/{id}/adjust', \App\Livewire\Supplies\StockAdjustment::class)->name('adjust');
    Route::get('/reports', \App\Livewire\Supplies\SupplyReports::class)->name('reports');
});

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
