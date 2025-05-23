<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Api\ArtikelController;
use App\Http\Controllers\Api\PengepulController;
use App\Http\Controllers\Api\JanjiTemuController;
use App\Http\Controllers\Api\DaftarhargaController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TransaksiController;
use App\Http\Controllers\Api\ProfileController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);
Route::post('logout',    [AuthController::class, 'logout']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'showProfile']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::resource('user', UserController::class);
    Route::resource('artikel', ArtikelController::class);
    Route::resource('pengepul', PengepulController::class);
    Route::post('/pengepul', [PengepulController::class, 'store']);
    Route::post('pengepul/import', [PengepulController::class, 'import']);
    Route::resource('janji-temu', JanjiTemuController::class)->only(['index', 'store','show','update','destroy']);
    Route::post('janji-temu/{id}/approve', [JanjiTemuController::class, 'approve']);
    Route::post('janji-temu/{id}/reject', [JanjiTemuController::class, 'reject']);
    Route::get('/task/by-pengepul', [TaskController::class, 'getTasksByPengepul']);
    Route::resource('harga', DaftarhargaController::class);
    Route::resource('task', TaskController::class);
    Route::resource('transaksi', TransaksiController::class);
    Route::post('/task/{id}/accepted', [TaskController::class, 'accepted']);
    Route::put('/task/{id}/location', [TaskController::class, 'updateLocation']);
});






