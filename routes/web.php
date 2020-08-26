<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/load', 'TestController@parsing');
Route::get('/settings', 'SettingController@index');
Route::get('/settings/load_categories_av_by', 'SettingController@loadCategoriesAvBy');
Route::get('/settings/get_categories_av_by', 'SettingController@getCategoriesAvBy');

Route::post('/settings/import_category', 'SettingController@importCategory');

Route::get('/settings/load_pages_cars', 'SettingController@getCarsPagesAvBy');

Route::get('/settings/get_categories', 'SettingController@getCategories');
Route::get('/settings/get_table', 'SettingController@getTable');

Route::post('/settings/import_cars', 'SettingController@importCars');



Route::get('/cron/parse', 'CronController@parse');
