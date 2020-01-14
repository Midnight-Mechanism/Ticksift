<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
| Middleware options can be located in `app/Http/Kernel.php`
|
*/

// Public routes
Route::group(['middleware' => ['web']], function () {

    Route::view('/', 'landing');

    Route::resource('simulations', 'SimulationController', [
        'except' => [
            'edit',
        ],
    ]);

    Route::get('securities/explorer', ['as' => 'securities.explorer', 'uses' => 'SecurityController@explorer']);
    Route::get('securities/momentum', ['as' => 'securities.momentum', 'uses' => 'SecurityController@momentum']);

    Route::get('portfolios/search', ['as' => 'portfolios.search', 'uses' => 'PortfolioController@search']);
    Route::get('securities/search', ['as' => 'securities.search', 'uses' => 'SecurityController@search']);

    Route::post('portfolios/securities', ['as' => 'portfolios.securities', 'uses' => 'PortfolioController@securities']);
    Route::post('securities/prices', ['as' => 'securities.prices', 'uses' => 'SecurityController@prices']);
    Route::post('securities/calculateMomentum', ['as' => 'securities.calculate-momentum', 'uses' => 'SecurityController@calculateMomentum']);
});
