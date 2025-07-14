<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class CustomLoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        if($request->is('admin/*')) {
            return redirect()->intended('/admin/attendance/list');
        }
        
        return redirect()->intended('/attendance');
    }
}