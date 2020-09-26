<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::match(['get', 'post'], '/settings/telegram_bot', 'TelegramController@index');
Route::match(['get', 'post'], '/settings/bot_telegram', 'TelegramController@index');

Route::match(['get', 'post'], '/settings/bot_telegram/set_brand', 'TelegramController@setBrand');
Route::match(['get', 'post'], '/settings/bot_telegram/set_model', 'TelegramController@setModel');
Route::match(['get', 'post'], '/settings/bot_telegram/set_year', 'TelegramController@setYear');
Route::match(['get', 'post'], '/settings/bot_telegram/set_price', 'TelegramController@setPrice');
Route::match(['get', 'post'], '/settings/bot_telegram/set_country', 'TelegramController@setCountry');
Route::match(['get', 'post'], '/settings/bot_telegram/set_region', 'TelegramController@setRegion');
Route::match(['get', 'post'], '/settings/bot_telegram/set_city', 'TelegramController@setCity');
