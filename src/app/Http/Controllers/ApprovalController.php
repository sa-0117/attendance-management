<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApprovalController extends Controller
{
    public function store(Request $request) {

        $user = auth()->user();
    
        Approval::create([
            'attendance_id' => $request->attendance_id,
            'user_id' => $user->id,
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'breaks' => $request->breaks,
            'remarks' =>$request->remarks,
            'status' => 'pending',
        ]);

        return redirect()->route('request.form', ['teb' => 'pending']);
    }
}
