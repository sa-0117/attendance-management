<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controlle
{
    public function approve($id) {
        $approval = Approval::findOrFile($id);
        $attendance = $approval->attendance;

        $attendance->update([
            'clock_in' => $approval->clock_in,
            'clock_out' => $approval->clock_out,
        ]);

        $attendance->breaks()->delete();
        foreach ($approval->breaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break['start'],
                'break_end' => $break['end'],
            ]);
        }

        $approval->update([
            'status' => 'approved',
        ]);

        return back();
    }


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
