<?php

use App\Models\User;
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

Route::get('/datetime', function () {
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $yesterday = date('Y-m-d', strtotime($date. ' - 1 days'));

    return response("Agora: {$date} {$time} - Ontem: {$yesterday}");
});
