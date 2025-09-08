<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
{
    $user = $request->user();

    if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            return redirect()->route('verification.notice');
        }

    if ($request->is('admin/*')) {
        return redirect('/admin/attendance/list');
    }

    return redirect('/attendance');
}

}