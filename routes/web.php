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

    // Landing Page
    Route::view('/', 'landing');

    // Activation Routes
    Route::get('/activate', ['as' => 'activate', 'uses' => 'Auth\ActivateController@initial']);

    Route::get('/activate/{token}', ['as' => 'authenticated.activate', 'uses' => 'Auth\ActivateController@activate']);
    Route::get('/activation', ['as' => 'authenticated.activation-resend', 'uses' => 'Auth\ActivateController@resend']);
    Route::get('/exceeded', ['as' => 'exceeded', 'uses' => 'Auth\ActivateController@exceeded']);

    Route::get('activate/{code}', ['as' => 'activate',   'uses' => 'UserController@activate']);
});

// Registered User Routes
Route::group(['middleware' => ['auth']], function () {

    //  Homepage Route - Redirect based on user role is in controller.
    Route::get('home', ['as' => 'public.home',   'uses' => 'UserController@index']);
    Route::get('/activation-required', ['uses' => 'Auth\ActivateController@activationRequired'])->name('activation-required');
    Route::get('logout', ['uses' => 'Auth\LoginController@logout'])->name('logout');

});

// Registered and is current user routes.
Route::group(['middleware' => ['auth', 'activated', 'currentUser']], function () {
    Route::get('portfolios/search', ['as' => 'portfolios.search', 'uses' => 'PortfolioController@search']);
    Route::get('securities/search', ['as' => 'securities.search', 'uses' => 'SecurityController@search']);
});

Route::group(['middleware' => ['auth', 'activated', 'activity', 'currentUser']], function () {

    Route::get('profile', ['as' => 'profile',   'uses' => 'UserController@profile']);
    Route::post('update-password', ['as' => 'update-password',   'uses' => 'UserController@updatePassword']);
    Route::post('update-profile', ['as' => 'update-profile',   'uses' => 'UserController@updateProfile']);

    Route::resource('simulations', 'SimulationController', [
        'except' => [
            'edit',
        ],
    ]);

    Route::get('securities/explorer', ['as' => 'securities.explorer', 'uses' => 'SecurityController@explorer']);
    Route::get('securities/momentum', ['as' => 'securities.momentum', 'uses' => 'SecurityController@momentum']);

    Route::post('portfolios/securities', ['as' => 'portfolios.securities', 'uses' => 'PortfolioController@securities']);
    Route::post('securities/prices', ['as' => 'securities.prices', 'uses' => 'SecurityController@prices']);
    Route::post('securities/calculateMomentum', ['as' => 'securities.calculate-momentum', 'uses' => 'SecurityController@calculateMomentum']);
});

// Registered and is admin routes.
Route::group(['middleware' => ['auth', 'activated', 'activity', 'role:admin']], function () {
});
