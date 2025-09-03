<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApprovalController;

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

Route::middleware(['auth:web'])->group(function (){
    Route::get('/attendance',[AttendanceController::class,'showAttendanceStatus'])->name('attendance.form');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('list.now');
    Route::get('/attendance/list/{date?}', [AttendanceController::class, 'index'])->name('attendance.list');
    Route::post('/attendance/start',[AttendanceController::class,'startWork'])->name('attendance.start');
    Route::post('/attendance/end',[AttendanceController::class,'endWork'])->name('attendance.end');
    Route::post('/break/start',[AttendanceController::class,'startBreak'])->name('break.start');
    Route::post('/break/end',[AttendanceController::class,'endBreak'])->name('break.end');
    Route::get('/attendance/list/{period}',[AttendanceController::class,'index'])->name('list.period');
});

Route::middleware(['auth.any:web,admin'])->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'showFormDetail'])
        ->name('attendance.detail');
    Route::post('/attendance/{id}', [ApprovalController::class, 'storeRequest'])
    ->name('attendance.request');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');
});

Route::middleware(['auth.any:web,admin'])->group(function () {
    Route::get('/stamp_correction_request/list',[ApprovalController::class,'requestList'])
        ->name('request.list');
    Route::post('/stamp_correction_request/list', [ApprovalController::class,'requestList'])
        ->name('request.list');
});


