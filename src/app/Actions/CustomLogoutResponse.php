<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        if ($request->is('admin/*')) {
            return redirect('/admin/login');
        }

        return redirect('/login');
    }
}
