<?php

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\IncotermController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ContinentController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\BillingNoteController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\PasswordChangeController;
use App\Http\Controllers\QuantityDescriptionController;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
// Route::get('/', function () {
//     return redirect('https://www.novalogisticsrl.com/');
// });
Route::get('/', function () {
    return redirect('/auth/login');
});

// Route::get('/', function () {
//     return view('welcome');
// });




//Route::post('/downloadOperation', [OperationController::class, 'downloadOperation'])->name('operations.download');
/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

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
        Route::get('/profile/ver', 'show')->name('profile.show');
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


    Route::prefix('audits')->name('audits.')->controller(AuditController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/history/{type}/{id}', 'history')->name('history');
    });
    // Gestión de usuarios
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
    });

    // Gestión de continentes
    Route::prefix('continents')->name('continents.')->controller(ContinentController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
    });

    // Gestión de países
    Route::prefix('countries')->name('countries.')->controller(CountryController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::patch('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
    });

    // Gestión de ciudades
    Route::prefix('cities')->name('cities.')->controller(CityController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::patch('/restore/{id}', 'restore')->name('restore');
        Route::delete('/force-delete/{id}', 'forceDelete')->name('forceDelete');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
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
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
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
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
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
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
    });

    //ExchangeRates
    Route::prefix('exchange-rates')->name('exchange-rates.')->controller(ExchangeRateController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
    });

    Route::prefix('quantity_descriptions')->name('quantity_descriptions.')->controller(QuantityDescriptionController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('toggle-status/{id}', 'toggleStatus')->name('toggleStatus');
    });

    // Reportes
    Route::get('/reports/quotations', [ReportController::class, 'quotationsReport'])->name('reports.quotations');
    Route::get('/reports/operations', [ReportController::class, 'operationsReport'])->name('reports.operations');

    // Exportación
    Route::post('/reportes/exportar/cotizaciones/excel', [ReportController::class, 'exportQuotationsExcel'])->name('reports.export.quotations.excel');
    Route::post('/reportes/exportar/operaciones/excel', [ReportController::class, 'exportOperationsExcel'])->name('reports.export.operations.excel');

});


/*
|--------------------------------------------------------------------------
| Rutas para Comerciales, Operadores y Administradores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,operator,commercial'])->group(function () {
    // Gestión de clientes
    Route::prefix('customers')->name('customers.')->controller(CustomerController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/create', 'create')->name('create');
        Route::get('/edit/{NIT}', 'edit')->name('edit');
        Route::get('/{NIT}', 'show')->name('show');
        Route::put('/{NIT}', 'update')->name('update');
        Route::delete('/{NIT}', 'destroy')->name('destroy');
    });

    // Gestión de cotizaciones
    Route::prefix('quotations')->name('quotations.')->controller(QuotationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('/storeCustomer', 'storeCustomer')->name('storeCustomer');
        Route::post('/storeQuantityDescripcion', 'storeQuantityDescripcion')->name('storeQuantityDescripcion');
        Route::get('/searchLocation', 'searchLocation')->name('searchLocation');
        Route::get('/searchCustomer', 'searchCustomer')->name('searchCustomer');
        Route::get('/searchQuantityDescription', 'searchQuantityDescription')->name('searchQuantityDescription');
        Route::post('/generateInternalQuotation', 'generateExcel')->name('generate.excel.download');
        Route::post('/generateQuotation', 'generarCotizacion')->name('generate.download');

        Route::get('/create', 'create')->name('create');
        Route::get('/{id}', 'show')->name('show');
        Route::get('/edit/{id}', 'edit')->name('edit');
        Route::put('/{id}', 'update')->name('update');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::patch('updateStatus/{id}', 'updateStatus')->name('updateStatus');
        Route::post('/customers/ajax-store', 'storeCustomer')
            ->name('customers.ajax-store');
    });
});

/*
|--------------------------------------------------------------------------
| Rutas para Operadores y Administradores
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,operator'])->group(function () {

    Route::prefix('operations')->group(function () {
        Route::get('/', [OperationController::class, 'index'])->name('operations.index');
        Route::get('/create', [OperationController::class, 'create'])->name('operations.create');
        Route::get('/searchQuotations', [OperationController::class, 'searchQuotations'])->name('operations.searchQuotations');
        Route::get('/show-quotation/{id}', [OperationController::class, 'showQuotation'])->name('operations.showQuotation');
        Route::get('/create/quotation/{id}', [OperationController::class, 'showCreateFromQuotation'])
            ->name('operations.store-from-quotation');
        Route::post('/create/quotation/{id}', [OperationController::class, 'storeFromQuotation'])
            ->name('operations.store-from-quotation');

        Route::get('/show/{id}', [OperationController::class, 'show'])->name('operations.show');
        Route::get('/edit/{id}', [OperationController::class, 'edit'])->name('operations.edit');
        Route::put('/{id}', [OperationController::class, 'update'])->name('operations.update');
        Route::delete('/{id}', [OperationController::class, 'destroy'])->name('operations.destroy');
        Route::post('/toggleStatus/{id}', [OperationController::class, 'toggleStatus'])
            ->name('operations.toggle-status');
        Route::post('/downloadOperation', [OperationController::class, 'downloadOperation'])->name('operations.download');
        Route::post('/invoiceWord', [InvoiceController::class, 'generateInvoiceFromBillingNote'])->name('invoice.download');
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

/*
|--------------------------------------------------------------------------
| Rutas para Comerciales
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:commercial'])->group(function () {
    // Dashboard
    Route::get("/commercial", [CommercialController::class, "index"])->name("commercial.dashboard");
});
