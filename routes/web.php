<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('/auth/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('/auth/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::get('/auth/new-password', [AuthController::class, 'showNewPasswordForm'])->name('new-password');


Route::get("/users", [UserController::class, "index"])->name("users.index");
Route::get("/users/create", [UserController::class, "create"])->name("users.create");
Route::post("/users/store", [UserController::class, "store"])->name("users.store");
Route::get("/users/edit", [UserController::class, "edit"])->name("users.edit");
Route::put("/users/update", [UserController::class, "update"])->name("users.update");
Route::delete("/users/destroy", [UserController::class, "destroy"])->name("users.destroy");

Route::get("/clients", [ClientController::class, "index"])->name("clients.index");
Route::get("/clients/create", [ClientController::class, "create"])->name("clients.create");
Route::post("/clients/store", [ClientController::class, "store"])->name("clients.store");
Route::get("/clients/edit", [ClientController::class, "edit"])->name("clients.edit");
Route::put("/clients/update", [ClientController::class, "update"])->name("clients.update");
Route::delete("/clients/destroy", [ClientController::class, "destroy"])->name("clients.destroy");

