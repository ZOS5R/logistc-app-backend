<?php
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckIfManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserInformationController;
use App\Http\Controllers\UserJobInfoController;
use App\Http\Controllers\AttendanceRecordController;

use App\Http\Controllers\ContactInformationController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RedemptionController;
use App\Http\Controllers\RewardItemController;

use App\Http\Controllers\DriverTripController;


Route::post('auth/login', [AuthController::class, 'login']);

 Route::post('auth/register', [AuthController::class, 'register'])->middleware('is_manger');

 Route::middleware('auth:sanctum')->post('user_informations', [UserInformationController::class, 'store']);
 Route::middleware('auth:sanctum')->get('user_informations', [UserInformationController::class, 'index']);

 Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user_job_info', [UserJobInfoController::class, 'index']);
    Route::post('/user_job_info', [UserJobInfoController::class, 'store']);
    Route::get('/user_job_info/{id}', [UserJobInfoController::class, 'show']);
    Route::put('/user_job_info/{id}', [UserJobInfoController::class, 'update']);
    Route::delete('/user_job_info/{id}', [UserJobInfoController::class, 'destroy']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/contact_informations', [ContactInformationController::class, 'index']);
    Route::post('/contact_informations', [ContactInformationController::class, 'store']);
    Route::get('/contact_informations/{id}', [ContactInformationController::class, 'show']);
    Route::put('/contact_informations/{id}', [ContactInformationController::class, 'update']);
    Route::delete('/contact_informations/{id}', [ContactInformationController::class, 'destroy']);
});



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attendance', [AttendanceRecordController::class, 'index']); // الحصول على سجلات الحضور والانصراف
    Route::post('/attendance', [AttendanceRecordController::class, 'store']); // إضافة سجل جديد
    Route::put('/attendance/{id}', [AttendanceRecordController::class, 'update']); // تحديث سجل الحضور والانصراف
    Route::delete('/attendance/{id}', [AttendanceRecordController::class, 'destroy']); // حذف سجل الحضور والانصراف
});

Route::middleware('auth:sanctum')->group(function() {
    Route::get('evaluations', [EvaluationController::class, 'index']);
    Route::post('evaluations', [EvaluationController::class, 'store']);
    Route::get('evaluations/{id}', [EvaluationController::class, 'show']);
    Route::put('evaluations/{id}', [EvaluationController::class, 'update']);
    Route::delete('evaluations/{id}', [EvaluationController::class, 'destroy']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/requests', [RequestController::class, 'store']); // تقديم طلب
    Route::get('/requests', [RequestController::class, 'index']); // تقديم طلب
    Route::patch('/requests/{id}/status', [RequestController::class, 'updateStatus']); // تحديث حالة الطلب
});



// مجموعة من المسارات محمية ب middelware auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // مسارات سجل الاستبدالات
    Route::get('/redemptions', [RedemptionController::class, 'index']);
    Route::post('/redemptions', [RedemptionController::class, 'store']);
    Route::patch('/redemptions/{id}/status', [RedemptionController::class, 'updateStatus']);
    Route::delete('/redemptions/{id}', [RedemptionController::class, 'destroy']);

    // مسارات عناصر المكافأة (للمدير في حالات الإضافة والتعديل والحذف)
    Route::get('/reward-items', [RewardItemController::class, 'index']);
    Route::post('/reward-items', [RewardItemController::class, 'store']);
    Route::get('/reward-items/{id}', [RewardItemController::class, 'show']);
    Route::put('/reward-items/{id}', [RewardItemController::class, 'update']);
    Route::delete('/reward-items/{id}', [RewardItemController::class, 'destroy']);
});
Route::get('/user_redemptions_with_points', [RedemptionController::class, 'getUserRedemptionsWithPoints']);



Route::post('/driver-trips', [DriverTripController::class, 'store']);
Route::patch('/driver-trips/{id}/status', [DriverTripController::class, 'updateStatus']);
