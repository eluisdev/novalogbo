<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\ContinentController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\ServiceController;
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
        Route::get('/profile/{id}', 'edit')->name('profile.edit');
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


//Route::get("/operator", [OperatorController::class, "index"])->name("operator.dashboard");

// Route::get("/continents", [ContinentController::class,"index"])->name("continents.index");
// Route::get("/continents/create", [ContinentController::class,"create"])->name("continents.create");
// Route::post("/continents", [ContinentController::class,"store"])->name("continents.store");
// Route::get("/continents/{id}", [ContinentController::class,"edit"])->name("continents.edit");
// Route::put("/continents/{id}", [ContinentController::class,"update"])->name("continents.update");
// Route::delete("/continents/{id}", [ContinentController::class,"destroy"])->name("continents.destroy");

// Route::get("/countries", [CountryController::class,"index"])->name("countries.index");
// Route::get("/countries/create", [CountryController::class,"create"])->name("countries.create");
// Route::post("/countries", [CountryController::class,"store"])->name("countries.store");
// Route::get("/countries/{id}", [CountryController::class,"edit"])->name("countries.edit");
// Route::put("/countries/{id}", [CountryController::class,"update"])->name("countries.update");
// Route::delete("/countries/{id}", [CountryController::class,"destroy"])->name("countries.destroy");

// Route::get("/cities", [CityController::class,"index"])->name("cities.index");
// Route::get("/cities/create", [CityController::class,"create"])->name("cities.create");
// Route::post("/cities", [CityController::class,"store"])->name("cities.store");
// Route::get("/cities/{id}", [CityController::class,"edit"])->name("cities.edit");
// Route::put("/cities/{id}", [CityController::class,"update"])->name("cities.update");
// Route::delete("/cities/{id}", [CityController::class,"destroy"])->name("cities.destroy");

// Route::get("/services", [ServiceController::class,"index"])->name("services.index");
// Route::get("/services/create", [ServiceController::class,"create"])->name("services.create");
// Route::post("/services", [ServiceController::class,"store"])->name("services.store");
// Route::get("/services/{id}", [ServiceController::class,"edit"])->name("services.edit");
// Route::put("/services/{id}", [ServiceController::class,"update"])->name("services.update");
// Route::delete("/services/{id}", [ServiceController::class,"destroy"])->name("services.destroy");

// Route::get("/incoterms", [IncotermController::class,"index"])->name("incoterms.index");
// Route::get("/incoterms/create", [IncotermController::class,"create"])->name("incoterms.create");
// Route::post("/incoterms", [IncotermController::class,"store"])->name("incoterms.store");
// Route::get("/incoterms/{id}", [IncotermController::class,"edit"])->name("incoterms.edit");
// Route::put("/incoterms/{id}", [IncotermController::class,"update"])->name("incoterms.update");
// Route::delete("/incoterms/{id}", [IncotermController::class,"destroy"])->name("incoterms.destroy");