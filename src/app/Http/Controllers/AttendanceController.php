<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function create()
    {
        return view('attendance');
    }

    public function index()
    {
        return view('list');
    }

    public function show()
    {
        return view('detail');
    }

    public function request()
    {
        return view('request');
    }
}
