<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function showAttendanceStatus() {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();
        
        $status = 'off';
        if ($attendance) {
            $status = $attendance->status;
        }

        return view('attendance', compact('status'));
    }

    public function startWork()
    {
        $user = auth()->user();
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now(),
            'status' => 'working'
        ]);
            return redirect()->route('attendance.form');
    }

    public function endWork()
    {
        $user = auth()->user();
        $attendance = Attendance::where('user_id', $user->id)
                        ->where('work_date', now()->format('Y-m-d'))
                        ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => now(),
                'status' => 'end'
            ]);
        }
        return redirect()->route('attendance.form');
    }

    public function startBreak() {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance) {
            $attendance->breaks()->create([
                'break_start' => now()
            ]);

            $attendance->update(['status' => 'break']);
        
        }
            return redirect()->route('attendance.form');
        
    }

    public function endBreak() {
        $user = auth()->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
                        ->where('work_date', $today)
                        ->first();

        if ($attendance) {
            // break_end がまだ null の最新レコードを更新
            $attendance->breaks()
                    ->whereNull('break_end')
                    ->latest()
                    ->first()
                    ->update(['break_end' => now()]);

            $attendance->update(['status' => 'working']);
        }

        return redirect()->route('attendance.form');
        
    }
    

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
