<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MessagesController;
use App\Http\Controllers\API\PublicFiguresController;
use App\Http\Controllers\API\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users/{id}/up', [UserController::class, 'update']);
Route::post('/users/{id}/del', [UserController::class, 'destroy']);

Route::post('/auth', [AuthController::class, 'index']);
Route::post('/exit', [AuthController::class, 'store']);

Route::get('/figures', [PublicFiguresController::class, 'index']);
Route::get('/figures/{id}', [PublicFiguresController::class, 'show']);
Route::post('/figures', [PublicFiguresController::class, 'store']);
Route::post('/figures/{id}/up', [PublicFiguresController::class, 'update']);
Route::post('/figures/{id}/del', [PublicFiguresController::class, 'destroy']);

Route::get('/messages', [MessagesController::class, 'index']);
Route::get('/messages/{id}', [MessagesController::class, 'store']);
Route::get('/{idSender}/messages/{idRecipient}/', [MessagesController::class, 'show']);
