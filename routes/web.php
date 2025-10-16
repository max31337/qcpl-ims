<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\UserManagement;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Analytics as AdminAnalytics;
use App\Livewire\Admin\TransferHistories;
use App\Livewire\Admin\ActivityLogs;
use App\Livewire\Profile;
use App\Livewire\DashboardRouter;

// Role-based dashboards
use App\Livewire\Roles\SupplyOfficer\Dashboard as SupplyOfficerDashboard;
use App\Livewire\Roles\PropertyOfficer\Dashboard as PropertyOfficerDashboard;
use App\Livewire\Roles\Staff\Dashboard as StaffDashboard;
use App\Livewire\Roles\Observer\Dashboard as ObserverDashboard;

Route::view('/', 'welcome')->name('welcome');

// Dashboard - unified wrapper that mounts the proper role-specific Livewire dashboard
Route::get('/dashboard', function () {
    return view('dashboard-wrapper');
})
    ->middleware(['auth', 'verified', 'mfa'])
    ->name('dashboard');

// Admin Dashboard
Route::get('/admin/dashboard', Dashboard::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin'])
    ->name('admin.dashboard');

// Role-Based Dashboards
Route::prefix('roles')->middleware(['auth', 'verified', 'mfa'])->name('roles.')->group(function () {
    Route::get('/supply-officer/dashboard', SupplyOfficerDashboard::class)
        ->middleware('check.role:supply_officer')
        ->name('supply-officer.dashboard');
    
    Route::get('/property-officer/dashboard', PropertyOfficerDashboard::class)
        ->middleware('check.role:property_officer')
        ->name('property-officer.dashboard');
    
    Route::get('/staff/dashboard', StaffDashboard::class)
        ->middleware('check.role:staff')
        ->name('staff.dashboard');
    
    Route::get('/observer/dashboard', ObserverDashboard::class)
        ->middleware('check.role:observer')
        ->name('observer.dashboard');
});

// Profile
Route::get('/profile', Profile::class)
    ->middleware(['auth', 'verified', 'mfa'])
    ->name('profile');

// Personal activity page for all authenticated users
Route::get('/activity', \App\Livewire\Activity\MyActivity::class)
    ->middleware(['auth', 'verified', 'mfa'])
    ->name('activity.me');

// Admin Analytics
Route::get('/admin/analytics', AdminAnalytics::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin,observer'])
    ->name('admin.analytics');

// Assets Analytics (allow property officers to view asset-only analytics)
Route::get('/assets/analytics', \App\Livewire\Assets\Analytics::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin,observer,property_officer'])
    ->name('assets.analytics');

// Admin - Transfer Histories
Route::get('/admin/transfer-histories', TransferHistories::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin,property_officer, observer'])
    ->name('admin.transfer-histories');

// Admin - User Management (Livewire component)
Route::get('/admin/invitations', UserManagement::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin'])
    ->name('admin.invitations');

// Admin - Activity Logs
Route::get('/admin/activity-logs', ActivityLogs::class)
    ->middleware(['auth', 'verified', 'mfa', 'check.role:admin,observer'])
    ->name('admin.activity-logs');

// Admin - Assets Reports
Route::middleware(['auth', 'verified', 'mfa', 'check.role:admin,property_officer,observer'])
    ->get('/admin/assets/reports', \App\Livewire\Assets\AssetReports::class)
    ->name('admin.assets.reports');

// Supplies Management Routes  
Route::middleware(['auth', 'verified', 'mfa'])->prefix('supplies')->name('supplies.')->group(function () {
    // Management routes (supply officers and staff only)
    Route::middleware('check.role:admin, supply_officer,staff')->group(function () {
        Route::get('/', \App\Livewire\Supplies\SupplyList::class)->name('index');
        Route::get('/create', \App\Livewire\Supplies\SupplyForm::class)->name('create');
        Route::get('/{id}/edit', \App\Livewire\Supplies\SupplyForm::class)->name('edit');
        Route::get('/{id}/adjust', \App\Livewire\Supplies\StockAdjustment::class)->name('adjust');
    });
    
    // Analytics (supply officers only)
    Route::get('/analytics', \App\Livewire\Supplies\SupplyAnalytics::class)
        ->middleware('check.role:supply_officer')
        ->name('analytics');
    
    // Reports (supply officers, staff, admin, and observers)
    Route::get('/reports', \App\Livewire\Supplies\SupplyReports::class)
        ->middleware('check.role:admin,supply_officer,staff,observer')
        ->name('reports');
});

// Assets Management Routes
Route::middleware(['auth', 'verified', 'mfa'])->prefix('assets')->name('assets.')->group(function () {
    // Management routes (admin and property officers only)
    Route::middleware('check.role:admin,property_officer')->group(function () {
        Route::get('/', action: \App\Livewire\Assets\AssetList::class)->name('index');
        Route::get('/create', \App\Livewire\Assets\AssetForm::class)->name('form');
        Route::get('/{assetId}/edit', \App\Livewire\Assets\AssetForm::class)->name('edit');
        Route::get('/{assetId}/transfer', \App\Livewire\Assets\AssetTransfer::class)->name('transfer');
    });
    
    // Read-only routes for observers (view history and transfer records)
    Route::middleware('check.role:admin,property_officer,observer')->group(function () {
        Route::get('/{assetId}/history', \App\Livewire\Assets\AssetHistory::class)->name('history');
        Route::get('/transfer-histories', TransferHistories::class)->name('transfer-histories');
    });
    
    // Reports (admin, property officers, and observers)
    Route::get('/reports', \App\Livewire\Assets\AssetReports::class)
        ->middleware('check.role:admin,property_officer,observer')
        ->name('reports');
});

// Load Breeze/Laravel auth routes (registers logout route)
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
