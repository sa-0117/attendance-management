<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view('/auth/login');
    }

    public function staff()
    {
        return view('staff_list');
    }

    public function show()
    {
        return view('staff');
    }
}