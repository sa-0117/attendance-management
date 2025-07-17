<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
            $attendance->breaks()
                    ->whereNull('break_end')
                    ->latest()
                    ->first()
                    ->update(['break_end' => now()]);

            $attendance->update(['status' => 'working']);
        }

        return redirect()->route('attendance.form');
        
    }

    //勤怠一覧画面
    public function index(Request $request, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::now();
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        $user = auth()->user();

        //今月分の勤怠取得
        $attendanceData = Attendance::with('breaks')->where('user_id', $user->id)->whereBetween('work_date', [$startOfMonth, $endOfMonth])->get()->keyBy('work_date');

        $week =['日','月','火','水','木','金','土'];

        //view表示データ
        $attendances = [];
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $attendance = $attendanceData->get($formattedDate);

            $breakSeconds = 0;
            if ($attendance && $attendance->breaks) {
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakSeconds += Carbon::parse($break->break_end)->diffInSeconds($break->break_start);
                    }
                }
            }

            // 勤務時間
            $workSeconds = 0;
            if ($attendance && $attendance->clock_in && $attendance->clock_out) {
                $workSeconds = Carbon::parse($attendance->clock_in)->diffInSeconds(Carbon::parse($attendance->clock_out)) - $breakSeconds;
                $workSeconds = max(0, $workSeconds);
            }

            $attendances[] = [
                'date' => $date,
                'day_of_week' => $week[$date->dayOfWeek],
                'clock_in' => optional($attendance)->clock_in,
                'clock_out' => optional($attendance)->clock_out,
                'break_time' => $breakSeconds,
                'work_time' => $workSeconds,
            ];
        }

        return view('list',[
            'attendances' => $attendances,
            'targetDate' => $targetDate,
        ]);
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
