<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

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
Route::post('register', [ApiController::class, 'register']);
Route::post('login', [ApiController::class, 'login']);

Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('profile', [ApiController::class, 'getUser']);
    Route::post('profile', [ApiController::class, 'editUser']);
    Route::delete('profile', [ApiController::class, 'removeUser']);

    Route::get('book', [ApiController::class, 'allBook']);
    Route::post('book', [ApiController::class, 'createBook']);
    Route::put('book', [ApiController::class, 'editBook']);
    Route::delete('book', [ApiController::class, 'removeBook']);

    Route::post('book/rent', [ApiController::class, 'rentBook']);
    Route::post('book/return', [ApiController::class, 'returnBook']);
    Route::get('book/status', [ApiController::class, 'bookStatus']);
    
});
