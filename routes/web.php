<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\UserProfileController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    // Login
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'store')->name('login.store');
    Route::post('/logout', 'logout')->name('logout');

    // Recuperación de contraseña
    Route::get('/forgot-password', 'showForgotPasswordForm')->name('password.request');
    Route::post('/forgot-password', 'sendResetLinkEmail')->name('password.email');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Rutas Protegidas (requieren autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    // Cambio de contraseña
    Route::controller(PasswordChangeController::class)->group(function () {
        Route::get('/password/change', 'showChangeForm')->name('password.change');
        Route::post('/password/change', 'changePassword')->name('password.change.store');
    });

    // Perfil de usuario
    Route::controller(UserProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::put('/profile', 'update')->name('profile.update');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para Administradores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // Gestión de usuarios
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/{id}/edit', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para Operadores y Administradores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,operator'])->group(function () {
    // Gestión de clientes
    Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{NIT}', 'show')->name('show');
        Route::get('/{NIT}/edit', 'edit')->name('edit');
        Route::put('/{NIT}', 'update')->name('update');
        Route::delete('/{NIT}', 'destroy')->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para Operadores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:operator'])->group(function () {
    // Dashboard
    Route::get('/operator', function () {
        return view('operator.dashboard');
    })->name('operator.dashboard');
});
