<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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

Route::prefix('admin')->middleware(['web', 'guest:admin'])->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::get('/admin/users/attendances',[UserController::class,'staff']);//修正必要!/admin/users/{user}/attendances メソッドも
Route::get('/admin/users',[UserController::class,'show']);

Route::middleware(['auth'])->group(function (){
    Route::get('/attendance',[AttendanceController::class,'showAttendanceStatus'])->name('attendance.form');
    Route::get('/attendance/list/{date?}', [AttendanceController::class, 'index'])->name('attendance.list');
    Route::post('/attendance/start',[AttendanceController::class,'startWork'])->name('attendance.start');
    Route::post('/attendance/end',[AttendanceController::class,'endWork'])->name('attendance.end');
    Route::post('/break/start',[AttendanceController::class,'startBreak'])->name('break.start');
    Route::post('/break/end',[AttendanceController::class,'endBreak'])->name('break.end');

    Route::get('/attendance/list',[AttendanceController::class,'index'])->name('list.now');
    Route::get('/attendance/list/{period}',[AttendanceController::class,'index'])->name('list.period');
    
    Route::get('/attendance/{id}',[AttendanceController::class,'showFromDetail'])->name('attendance.detail.show');
    Route::post('/attendance/{id}',[AttendanceController::class,'editFromDetail'])->name('attendance.detail.edit');

    Route::get('/stamp_correction_request/list',[AttendanceController::class,'requestForm'])->name('request.form');


});

Route::get('/admin/attendances', [AdminController::class,'index']);
Route::get('/admin/attendances/detail', [AdminController::class,'show']);//修正必要!/admin/attendances/{id}
Route::get('/admin/requests',[AdminController::class,'request']);//メソッド名修正必要
Route::get('/admin/requests/approvals',[AdminController::class,'approvals']);//　/admin/requests/{id} メソッド名も修正必要

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
