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

// Homepage Route
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Auth::routes();

// Public Routes
Route::group(['middleware' => ['web']], function () {
    Route::get('activate/{code}', ['as' => 'activate',   'uses' => 'UserController@activate']);
});

// Registered User Routes
Route::group(['middleware' => ['auth']], function () {

    //  Homepage Route - Redirect based on user role is in controller.
    Route::get('home', ['as' => 'public.home',   'uses' => 'UserController@index']);
    Route::get('logout', ['uses' => 'Auth\LoginController@logout'])->name('logout');

});

// Registered and is current user routes.
Route::group(['middleware' => ['auth', 'activated', 'currentUser']], function () {
    Route::get('securities/search', ['as' => 'securities.search', 'uses' => 'SecurityController@search']);
});

Route::group(['middleware' => ['auth', 'activated', 'activity', 'currentUser']], function () {

    Route::get('profile', ['as' => 'profile',   'uses' => 'UserController@profile']);
    Route::post('change-password', ['as' => 'update-password',   'uses' => 'UserController@updatePassword']);

    Route::resource('simulations', 'SimulationController', [
        'except' => [
            'edit',
        ],
    ]);

    Route::view('securities/show', 'securities.show')->name('securities.show');
    Route::get('securities/{security}/prices', ['as' => 'securities.price', 'uses' => 'SecurityController@prices']);
});

// Registered and is admin routes.
Route::group(['middleware' => ['auth', 'activated', 'activity', 'role:admin']], function () {
});
