<?php
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckIfManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserInformationController;
use App\Http\Controllers\UserJobInfoController;


Route::post('/login', [AuthController::class, 'login']);

 Route::post('/register', [AuthController::class, 'register'])->middleware('is_manger');

 Route::middleware('auth:sanctum')->post('user_informations', [UserInformationController::class, 'store']);
 Route::middleware('auth:sanctum')->get('user_informations', [UserInformationController::class, 'index']);

 Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user_job_info', [UserJobInfoController::class, 'index']);
    Route::post('/user_job_info', [UserJobInfoController::class, 'store']);
    Route::get('/user_job_info/{id}', [UserJobInfoController::class, 'show']);
    Route::put('/user_job_info/{id}', [UserJobInfoController::class, 'update']);
    Route::delete('/user_job_info/{id}', [UserJobInfoController::class, 'destroy']);
});
