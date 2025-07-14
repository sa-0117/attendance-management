<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showAdminLoginForm() {
        return view('auth/admin_login');
    }
}
