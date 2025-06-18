<?php

use App\Http\Controllers\AtvsldController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/atvsld/areas', [AtvsldController::class, 'getAreas']);
Route::get('/atvsld/get-emails', [AtvsldController::class, 'getEmails']);
Route::get('/atvsld/evaluate', [AtvsldController::class, 'evaluate']);
Route::post('/atvsld/store', [AtvsldController::class, 'store']);
Route::delete('/atvsld/{id}', [AtvsldController::class, 'destroy']);
Route::put('/atvsld/{id}', [AtvsldController::class, 'update']);
Route::get('/atvsld/reports', [AtvsldController::class, 'reports']);
Route::get('/atvsld/get-solution', [AtvsldController::class, 'getSolution']);
Route::get('/atvsld/scheduler', [AtvsldController::class, 'scheduler']);
Route::post('/atvsld/change-password', [AuthController::class, 'changePassword']);
Route::post('/atvsld/solution', [AtvsldController::class, 'submitSolution']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/atvsld/auto-success', [AtvsldController::class, 'autoSuccess']);
