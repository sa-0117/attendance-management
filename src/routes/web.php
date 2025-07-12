<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

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

Route::get('/admin/users/attendances',[UserController::class,'staff']);//修正必要!/admin/users/{user}/attendances メソッドも
Route::get('/admin/users',[UserController::class,'show']);

Route::get('/attendance',[AttendanceController::class,'create']);
Route::get('/attendance/list',[AttendanceController::class,'index']);
Route::get('/attendance/detail',[AttendanceController::class,'show']);//修正必要!/attendance/detail/{id}
Route::get('/stamp_correction_request/list',[AttendanceController::class,'request']);//メソッド名修正必要

Route::get('/admin/attendances', [AdminController::class,'index']);
Route::get('/admin/attendances/detail', [AdminController::class,'show']);//修正必要!/admin/attendances/{id}
Route::get('/admin/requests',[AdminController::class,'request']);//メソッド名修正必要
Route::get('/admin/requests/approvals',[AdminController::class,'approvals']);//　/admin/requests/{id} メソッド名も修正必要

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');
