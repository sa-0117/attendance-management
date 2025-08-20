<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserController extends Controller
{

    public function show()
    {
        $users = DB::table('users')->get();

        return view('staff_list', compact('users'));
    }
}