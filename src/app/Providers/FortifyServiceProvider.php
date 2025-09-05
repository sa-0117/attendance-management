<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\LoginResponse;
use App\Actions\Fortify\CustomLoginResponse;
use App\Models\User;
use App\Models\Admin;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Actions\Fortify\CustomLogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request)
            {
                return redirect()->route('verification.notice');
            }
        });

        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(function () {
                return view('auth.register');
        });
            
        Fortify::loginView(function (Request $request) {
            return $request->is('admin/*')
            ? view('auth.admin_login')
            : view('auth.login');
        });

        Fortify::authenticateUsing(function (Request $request) {
            if ($request->is('admin/*')) {
                $admin = \App\Models\Admin::where('email', $request->email)->first();
                if ($admin && \Hash::check($request->password, $admin->password)) {
                    \Auth::guard('admin')->login($admin, $request->remember);
                    return $admin;
                }
            } else {
                $user = \App\Models\User::where('email', $request->email)->first();
                if ($user && \Hash::check($request->password, $user->password)) {
                    \Auth::guard('web')->login($user, $request->remember);
                    return $user;
                }
            }
            return null;
        });
            
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        app()->bind(FortifyLoginRequest::class, function ($app) {
            $request = $app->make(Request::class);
            if ($request->is('admin/*')) {
                return $app->make(AdminLoginRequest::class);
            }
            return $app->make(UserLoginRequest::class);
        });
    }
}