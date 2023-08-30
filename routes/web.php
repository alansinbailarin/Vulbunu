<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\UserAccountController;
use App\Http\Controllers\TalentController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\UserLocation;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Iniciar sesion con otros provedores
Route::get('/auth/{provider}/redirect', [SocialController::class, 'redirect'])->where('provider', 'google|facebook');

Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])->where('provider', 'google|facebook');

// Ruta get para la página de inicio
Route::get('/', [IndexController::class, 'index']);
Route::post('/subscribe', [IndexController::class, 'store'])->name('index.store');

// Rutas para la pagina de los empleos
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
Route::get('/jobs/{job}', [IndexController::class, 'show'])->name('jobs.show');
Route::get('/publish-job', [JobController::class, 'create'])->name('jobs.create')->middleware('auth');
Route::post('/publish-job', [JobController::class, 'store'])->name('jobs.store')->middleware('auth');

// Rutas para logearse
Route::get('login', [AuthController::class, 'create'])->name('login')->middleware('guest');
Route::post('login', [AuthController::class, 'store'])->name('login.store');

// Ruta para cerrar sesión
Route::delete('logout', [AuthController::class, 'destroy'])->name('login.destroy');

// Ruta para crear perfil
Route::resource('user-account', UserAccountController::class)->only(['create', 'store'])->middleware('guest');

Route::get('/user-account/configuration', [UserAccountController::class, 'index'])->name('user-account.index')->middleware('auth');
Route::put('/user-account/configuration', [UserAccountController::class, 'update'])->name('user-account.update')->middleware('auth');
Route::delete('/user-account/configuration/{itemToDelete}', [UserAccountController::class, 'deleteItem'])->where('itemToDelete', 'avatar|cv')->name('user-account.destroy')->middleware('auth');
Route::put('/user-account/configuration/location', [UserLocation::class, 'update'])->name('location.update')->middleware('auth');

// Route::delete('/user-account/configuration', [UserAccountController::class, 'deleteCV'])->name('user-account.deleteCV')->middleware('auth');

// Rutas para los talentos
Route::get('/talents', [TalentController::class, 'index'])->name('talents.index');
Route::get('/talents/{talent}', [TalentController::class, 'show'])->name('talents.show');

// Admin dashboard
Route::get('/dashboard', [AdminController::class, 'dashboard'])->middleware('auth');
