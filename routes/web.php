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
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('pagespeed/critical-css', 'Pagespeed\CriticalCssController@index');
Route::get('pagespeed/critical-css/generate', 'Pagespeed\CriticalCssController@generate')
    ->middleware('throttle:1,1');
