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
use Symfony\Component\HttpFoundation\StreamedResponse;

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


    public function showAttendanceStaff(Request $request, $id) {

        // 選択されたスタッフ
        $user = User::findOrfail($id);

        $dateParam = $request->input($id);

        // パラメータがあればその日付をCarbonに変換、なければ今日
        $dateParam = $request->input('date');
        $targetDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();

        $targetDate = $targetDate->copy()->startOfMonth();

        $startOfMonth = $targetDate->copy();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);
        

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

    public function attendanceStaffCsv(Request $request, $id) {
        $user = User::findOrFail($id);

        $dateParam = $request->input('date');
        $targetDate = $dateParam ? Carbon::parse($dateParam) : Carbon::now();
        $startOfMonth = $targetDate->copy()->startOfMonth();
        $endOfMonth = $targetDate->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        $csvHeader = ['日付', '出勤', '退勤', '休憩時間', '合計'];

        $response = new StreamedResponse(function () use ($csvHeader, $attendances, $user) {
            $createCsvFile = fopen('php://output', 'w');

            mb_convert_variables('SJIS-win', 'UTF-8', $csvHeader);
            fputcsv($createCsvFile, $csvHeader);
    
            foreach ($attendances as $attendance) {
                $breakSeconds = $attendance->breaks->reduce(function($carry, $break) {
                    return $carry + ($break->break_start && $break->break_end
                        ? Carbon::parse($break->break_end)->diffInSeconds(Carbon::parse($break->break_start))
                        : 0);
                }, 0);

                $workSeconds = ($attendance->clock_in && $attendance->clock_out)
                    ? Carbon::parse($attendance->clock_in)->diffInSeconds(Carbon::parse($attendance->clock_out)) - $breakSeconds
                    : 0;

                $row =  [
                    Carbon::parse($attendance->work_date)->format('Y-m-d'),
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    gmdate('H:i', $breakSeconds),
                    gmdate('H:i', max(0, $workSeconds)),
                ];

                mb_convert_variables('SJIS-win', 'UTF-8', $row);
                fputcsv($createCsvFile, $row);
            }
            
            fclose($createCsvFile);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $user->name . $targetDate->format('Y_m') .'.csv"',
        ]);

        return $response;
    }



}