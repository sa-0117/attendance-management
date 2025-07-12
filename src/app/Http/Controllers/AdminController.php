<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin_list');
    }

    public function show()
    {
        return view('admin_detail');
    }

    public function request()
    {
        return view('admin_request');
    }

    public function approvals()
    {
        return view('approvals');
    }
}
