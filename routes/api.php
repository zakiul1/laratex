<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and are all
| assigned the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->get('/ping', function () {
    return response()->json(['status' => 'ok']);
});
