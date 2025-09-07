<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApprovalController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

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

Route::middleware(['auth:web', 'verified'])->group(function (){
    Route::get('/attendance',[AttendanceController::class,'showAttendanceStatus'])->name('attendance.form');
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('list.now');
    Route::get('/attendance/list/{date?}', [AttendanceController::class, 'index'])->name('attendance.list');
    Route::post('/attendance/start',[AttendanceController::class,'startWork'])->name('attendance.start');
    Route::post('/attendance/end',[AttendanceController::class,'endWork'])->name('attendance.end');
    Route::post('/break/start',[AttendanceController::class,'startBreak'])->name('break.start');
    Route::post('/break/end',[AttendanceController::class,'endBreak'])->name('break.end');
    Route::get('/attendance/list/{period}',[AttendanceController::class,'index'])->name('list.period');
});

Route::middleware(['auth.any:admin,web'])->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'showFormDetail'])
        ->name('attendance.detail');
    Route::post('/attendance/{id}', [ApprovalController::class, 'storeRequest'])
    ->name('attendance.request');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');
});

Route::middleware(['auth.any:admin,web'])->group(function () {
    Route::get('/stamp_correction_request/list',[ApprovalController::class,'requestList'])
        ->name('request.list');
    Route::post('/stamp_correction_request/list', [ApprovalController::class,'requestList'])
        ->name('request.list');
});


Route::middleware(['auth:web'])->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify_email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/attendance'); 
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');

    Route::get('/email/verify/check', function () {
        if (auth()->user()->hasVerifiedEmail()) {
            return redirect('/attendance'); 
        }

        return back();  
    })->name('verification.check');
});


