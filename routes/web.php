<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ContinentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\PasswordChangeController;

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

//TODO:CRUD DE LAS COTIZACIONES
Route::prefix('auth')->controller(AuthController::class)->group(function () {
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
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Gestión de continentes
    Route::prefix('continents')->name('continents.')->controller(ContinentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::patch('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
    });

    // Gestión de países
    Route::prefix('countries')->name('countries.')->controller(CountryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::patch('/restore/{id}', 'restore')->name('restore');
    });

    // Gestión de ciudades
    Route::prefix('cities')->name('cities.')->controller(CityController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::patch('/restore/{id}', 'restore')->name('restore');

    });

    // Gestión de Incoterms
    Route::prefix('incoterms')->name('incoterms.')->controller(IncotermController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::put('/{id}', 'update')->name('update');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('incoterms.toggleStatus');
    });

    // Gestión de servicios
    Route::prefix('services')->name('services.')->controller(ServiceController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('services.toggleStatus');
    });

    // Gestión de costos
    Route::prefix('costs')->name('costs.')->controller(CostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('costs.toggleStatus');
    });

});
//TODO: middelware admini y operador funciona
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
        Route::get('/edit/{NIT}', 'edit')->name('edit');
        Route::put('/{NIT}', 'update')->name('update');
        Route::delete('/{NIT}', 'destroy')->name('destroy');

        // POR VERSE
        Route::get('/search', 'search')->name('search');
    });


    // Gestión de cotizaciones
    Route::prefix('quotations')->name('quotations.')->controller(QuotationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        //Route::post('/', 'store')->name('store');
        //Route::get('/{id}', 'show')->name('show');
        //Route::get('/edit/{id}', 'edit')->name('edit');
        //Route::put('/{id}', 'update')->name('update');
        //Route::delete('/{id}', 'destroy')->name('destroy');

        Route::get('/get-countries/{continent}', 'getCountries')->name('getCountries');
        Route::get('/get-cities/{country}', 'getCities')->name('getCities');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para Operadores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:operator'])->group(function () {
    // Dashboard
    Route::get("/operator", [OperatorController::class, "index"])->name("operator.dashboard");
});
