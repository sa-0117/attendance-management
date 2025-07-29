<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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

// 管理者ログイン
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin'])
    ->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin']);

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('admin.logout');