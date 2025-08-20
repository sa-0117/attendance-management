<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Approval;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;

class AdminController extends Controller
{
    //勤怠一覧画面
    public function index(Request $request, $date = null)
    {
        $targetDate = $date ? Carbon::parse($date) : Carbon::now();
        $startOfDay = $targetDate->copy()->startOfDay();
        $endOfDay = $targetDate->copy()->endOfDay();

        $attendances = Attendance::with('user', 'breaks')
            ->whereBetween('work_date', [$startOfDay, $endOfDay])
            ->get();

        return view('admin_list',[
            'targetDate' => $targetDate,
            'attendances' => $attendances,
        ]);
    }


    public function showAttendanceStaff(Request $request, $id, $date = null ) {

        $targetDate = $date ? Carbon::parse($date) : Carbon::now();
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        // 選択されたスタッフ
        $user = User::findOrfail($id);

        // 今月分の勤怠取得
        $attendanceData = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function($item) {
                return \Carbon\Carbon::parse($item->work_date)->format('Y-m-d');
            });

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
                'id' => optional($attendance)->id,
                'user_id' => $user->id,
                'date' => $date,
                'day_of_week' => $week[$date->dayOfWeek],
                'clock_in' => optional($attendance)->clock_in,
                'clock_out' => optional($attendance)->clock_out,
                'break_time' => $breakSeconds,
                'work_time' => $workSeconds,
            ];
        } 

        return view('staff_attendance_list',[
            'attendances' => $attendances,
            'targetDate' => $targetDate,
            'user' => $user,
        ]);
    }
}
