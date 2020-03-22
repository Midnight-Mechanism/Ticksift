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

// Authentication Routes
Auth::routes();

// Public Routes
Route::group(['middleware' => ['web']], function () {

    Route::redirect('/', 'securities/momentum');

    // Activation Routes
    Route::get('activate', 'Auth\ActivateController@initial')->name('activate');
    Route::get('activate/{token}', 'Auth\ActivateController@activate')->name('authenticated.activate');
    Route::get('activation', 'Auth\ActivateController@resend')->name('authenticated.activation-resend');
    Route::get('exceeded', 'Auth\ActivateController@exceeded')->name('exceeded');

    Route::get('securities/explorer', 'SecurityController@explorer')->name('securities.explorer');
    Route::get('securities/momentum', 'SecurityController@momentum')->name('securities.momentum');

    Route::get('securities/find', 'SecurityController@find')->name('securities.find');
    Route::get('securities/search', 'SecurityController@search')->name('securities.search');
    Route::get('portfolios/search', 'PortfolioController@search')->name('portfolios.search');

    Route::get('portfolios/securities', 'PortfolioController@securities')->name('portfolios.securities');
    Route::get('securities/prices', 'SecurityController@prices')->name('securities.prices');
    Route::get('securities/get-momentum', 'SecurityController@getMomentum')->name('securities.get-momentum');

    Route::post('securities/store-chart-options', 'SecurityController@storeChartOptions')->name('securities.store-chart-options');

});

// Registered User Routes
Route::group(['middleware' => ['auth']], function () {

    //  Homepage Route - Redirect based on user role is in controller.
    Route::get('home', 'UserController@index')->name('public.home');
    Route::get('/activation-required', ['uses' => 'Auth\ActivateController@activationRequired'])->name('activation-required');
    Route::get('/activation-required', 'Auth\ActivateController@activationRequired')->name('activation-required');
    Route::get('logout', 'Auth\LoginController@logout')->name('logout');

    Route::get('profile', 'UserController@profile')->name('profile');
    Route::post('update-password', 'UserController@updatePassword')->name('update-password');
    Route::post('update-profile', 'UserController@updateProfile')->name('update-profile');

    Route::resource('portfolios', 'PortfolioController', [
        'except' => [
            'create',
            'show',
            'edit',
        ],
    ]);

});

// Registered and is admin routes.
Route::group(['middleware' => ['auth', 'activated', 'activity', 'role:admin']], function () {
});
