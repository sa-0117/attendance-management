<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ApprovalController;
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

// 管理者ログイン
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin']);

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('admin.logout');


Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin.list.now');
    Route::get('/admin/attendance/list/{date?}', [AdminController::class, 'index'])->name('attendance.admin.list');//日付を受け取るルート
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}',[ApprovalController::class,'showApprovalForm'])->name('approval.show');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}',[ApprovalController::class,'approve'])->name('approval.approve');

    Route::get('/admin/staff/list',[UserController::class,'show'])->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}',[AdminController::class, 'showAttendanceStaff'])->name('admin.attendance.staff');

});