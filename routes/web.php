<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserProfileController;

Route::get('/', function () {
    return view('welcome');
});

// LOGIN
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'store'])->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// OlVIDAR PASSWORD
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth', 'role:admin'])->group(function () {


    // Rutas para los paneles de control
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');


    // Rutas para Usuarios (User)
    Route::prefix('users')->group(function () {
        // Mostrar listado (con filtros)
        Route::get('/', [UserController::class, 'index'])->name('users.index');

        // Mostrar formulario de creación
        Route::get('/create', [UserController::class, 'create'])->name('users.create');

        // Guardar nuevo usuario (POST)
        Route::post('/', [UserController::class, 'store'])->name('users.store');

        // Mostrar detalles de un usuario
        Route::get('/{id}', [UserController::class, 'show'])->name('users.show');

        // Mostrar formulario de edición
        Route::get('/{id}/editar', [UserController::class, 'edit'])->name('users.edit');

        // Actualizar usuario (PUT/PATCH)
        Route::put('/{id}', [UserController::class, 'update'])->name('users.update');

        // Eliminar usuario (DELETE)
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});


Route::middleware(['auth', 'role:admin,operator'])->group(function () {

    // Rutas para el perfil de usuario
    Route::get('/profile', [UserProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::put('/profile', [UserProfileController::class, 'update'])
        ->name('profile.update');


    // Rutas para Clientes (Customer)
    Route::prefix('customers')->group(function () {
        // Mostrar listado (con filtros)
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');

        // Mostrar formulario de creación
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');

        // Guardar nuevo cliente (POST)
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');

        // Mostrar detalles de un cliente
        Route::get('/{NIT}', [CustomerController::class, 'show'])->name('customers.show');

        // Mostrar formulario de edición
        Route::get('/{NIT}/editar', [CustomerController::class, 'edit'])->name('customers.edit');

        // Actualizar cliente (PUT/PATCH)
        Route::put('/{NIT}', [CustomerController::class, 'update'])->name('customers.update');

        // Eliminar cliente (DELETE)
        Route::delete('/{NIT}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });
});

Route::middleware(['auth', 'role:operator'])->group(function () {
    // Rutas para el panel de control del cliente

    Route::get('/operator', function () {
        return view('operator.dashboard');
    })->name('operator.dashboard');

});
