<?php

use App\Http\Controllers\AdminSessionController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuUserPermissionController;
use App\Http\Controllers\SecurityCompanyController;
use App\Http\Controllers\SecurityProfileController;
use App\Http\Controllers\SecurityUserController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AdminSessionController::class, 'create'])->name('admin-session.create');
Route::post('/login', [AdminSessionController::class, 'store'])
    ->middleware('throttle:pbi-admin-login')
    ->name('admin-session.store');

Route::middleware('pbi.admin')->group(function (): void {
    Route::delete('/logout', [AdminSessionController::class, 'destroy'])->name('admin-session.destroy');

    Route::redirect('/', '/security-users');

    Route::resource('security-users', SecurityUserController::class)
        ->parameters(['security-users' => 'securityUser'])
        ->except('show');
    Route::resource('security-profiles', SecurityProfileController::class)
        ->parameters(['security-profiles' => 'securityProfile'])
        ->except('show');
    Route::resource('security-companies', SecurityCompanyController::class)
        ->parameters(['security-companies' => 'securityCompany'])
        ->except('show');
    Route::resource('menu-items', MenuItemController::class)->except('show');
    Route::resource('menu-permissions', MenuUserPermissionController::class)
        ->parameters(['menu-permissions' => 'menuPermission'])
        ->except('show');
});
