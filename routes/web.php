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
// use Twilio;

// Route::get('/', function () {
//     $timestamp = "2013-09-30";
//     dd(date("D,F jS", strtotime($timestamp)));

//     Twilio::message('+923065555700', 'LOL');
//     return view('welcome');
// });

Route::get('/thumb/{id}', 'OrderController@thumbDisplay');