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

// Load Breeze/Laravel auth routes (registers logout route)
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
