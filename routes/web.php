<?php

use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Home');
});

Route::get('securities/explorer', [SecurityController::class, 'explorer'])->name('securities.explorer');
Route::get('securities/momentum', [SecurityController::class, 'momentum'])->name('securities.momentum');

Route::get('securities/search', [SecurityController::class, 'search'])->name('securities.search');

Route::get('securities/prices', [SecurityController::class, 'prices'])->name('securities.prices');
Route::post('securities/momentum', [SecurityController::class, 'momentumResults'])->name('securities.momentum-results');

Route::get('indicators/recessions', [IndicatorController::class, 'recessions'])->name('indicators.recessions');

// activated users
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('profile', [UserController::class, 'profile'])->name('profile');
    Route::post('update-password', [UserController::class, 'updatePassword'])->name('update-password');
    Route::post('update-profile', [UserController::class, 'updateProfile'])->name('update-profile');

    Route::get('portfolios/securities', [PortfolioController::class, 'securities'])->name('portfolios.securities');
    Route::resource('portfolios', PortfolioController::class, [
        'except' => [
            'create',
            'show',
            'edit',
        ],
    ]);
});

require __DIR__.'/auth.php';
