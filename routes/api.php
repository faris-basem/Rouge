<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WelcomeController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('register','App\Http\Controllers\UserController@register');
Route::post('login','App\Http\Controllers\UserController@login');
Route::post('logout','App\Http\Controllers\UserController@logout');
Route::post('forgot','App\Http\Controllers\UserController@forgot');
Route::post('change_pass','App\Http\Controllers\UserController@chang_pass');

Route::post('new_address','App\Http\Controllers\AddressController@new_address');
Route::post('change_address','App\Http\Controllers\AddressController@change_address');
Route::get('my_addresses','App\Http\Controllers\AddressController@my_addresses');
Route::post('update_address','App\Http\Controllers\AddressController@update_address');
Route::post('delete_address','App\Http\Controllers\AddressController@delete_address');

Route::get('all_categories','App\Http\Controllers\ProductController@all_categories');
Route::get('all_categories_with_products','App\Http\Controllers\ProductController@all_categories_with_products');
Route::post('get_category_by_id','App\Http\Controllers\ProductController@get_category_by_id');
Route::post('search_in_category','App\Http\Controllers\ProductController@search_in_category');
Route::post('search_filter','App\Http\Controllers\ProductController@search_filter');

Route::post('add_to_cart','App\Http\Controllers\CartController@add_to_cart');
Route::get('my_cart','App\Http\Controllers\CartController@my_cart');

Route::post('checkout','App\Http\Controllers\OrderController@checkout');
Route::get('place_order','App\Http\Controllers\OrderController@place_order');
Route::get('OnProgressOrders', [OrderController::class, 'OnProgressOrders']);
Route::get('CompletedOrders', [OrderController::class, 'CompletedOrders']);
Route::post('order_by_id', [OrderController::class, 'order_by_id']);
Route::post('order_review','App\Http\Controllers\OrderController@order_review');

Route::get('wellcome', 'App\Http\Controllers\WelcomeController@wellcom');
Route::get('banner', [WelcomeController::class, 'banner']);
Route::get('privacy', [WelcomeController::class, 'privacy']);
Route::get('about_us', [WelcomeController::class, 'about_us']);

Route::post('search','App\Http\Controllers\ProductController@search');
Route::post('add_to_white_list','App\Http\Controllers\ProductController@add_white_list');
Route::get('my_white_list','App\Http\Controllers\ProductController@my_white_list');
Route::post('get_product_by_id','App\Http\Controllers\ProductController@get_product_by_id');
