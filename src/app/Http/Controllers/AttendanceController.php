<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\Approval;
use App\Models\BreakTime;
use App\Models\User;
use App\Http\Requests\DetailRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    public function showAttendanceStatus() {
        $user = auth('web')->user();
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
        $user = auth('web')->user();
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
        $user = auth('web')->user();
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
        $user = auth('web')->user();
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
        $user = auth('web')->user();
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
        $user = auth('web')->user();

        //今月分の勤怠取得
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

    public function showFormDetail(Request $request, $id) 
{
    // 管理者ログイン時
    if (auth('admin')->check()) {

        if ($id === 'new' || $id == 0) {
            $staffId = $request->query('staff_id');
            $workDate = $request->query('date', now()->toDateString());

            $attendance = new Attendance([
                'user_id' => $staffId,
                'work_date' => $workDate,
                'status' => 'off',
            ]);
            $user = User::find($staffId);

        } else {
            $attendance = Attendance::with('breaks', 'approval', 'user')->find($id);

            if (!$attendance) {
                abort(404);
            }

            $user = $attendance->user;
        }

        // BreakTime 初期化（最低2枠）
        $breaks = collect($attendance->breaks ?? []);
        if ($breaks->isEmpty()) {
            $breaks = collect([
                new \App\Models\BreakTime(['break_start' => null, 'break_end' => null])
            ]);
        }

        $minBreaks = 2;
        for ($i = $breaks->count(); $i < $minBreaks; $i++) {
            $breaks->push(new \App\Models\BreakTime(['break_start' => null, 'break_end' => null]));
        }
        $attendance->setRelation('breaks', $breaks);

        // 承認保留の取得
        $pendingApproval = Approval::where('attendance_id', $attendance->id ?? 0)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        return view('admin_detail', [
            'attendance' => $attendance,
            'user' => $attendance->user ?? User::find($staffId),
            'id' => $attendance->user->id ?? $staffId,
            'approval' => $pendingApproval,
            'breaks' => $breaks
        ]);
    }


    // 一般ユーザーログイン時
    if (auth('web')->check()) {
        $user = auth('web')->user();

        $attendance = Attendance::with('breaks')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$attendance) {
            $workDate = $request->query('date', now()->toDateString());
            $attendance = new Attendance([
                'user_id' => $user->id,
                'work_date' => $workDate,
                'status' => 'off',
            ]);
        }

        // BreakTime 初期化
        $pendingApproval = Approval::where('attendance_id', $attendance->id ?? 0)
            ->where('status', 'pending')
            ->first();

        if ($pendingApproval) {
            $attendance->clock_in = $pendingApproval->clock_in;
            $attendance->clock_out = $pendingApproval->clock_out;
            $attendance->remarks = $pendingApproval->remarks;

            $breaks = collect($pendingApproval->breaks ?? [])
                ->filter(fn($b) => !empty($b['start']) || !empty($b['end']))
                ->map(fn($b) => new \App\Models\BreakTime([
                    'break_start' => $b['start'] ?? null,
                    'break_end' => $b['end'] ?? null,
                ]));
        } else {
            $breaks = $attendance->breaks->isEmpty() ? collect([
                new \App\Models\BreakTime(['break_start' => null, 'break_end' => null])
            ]) : collect($attendance->breaks);
        }

        // 最低 2 枠確保
        $minBreaks = 2;
        for ($i = $breaks->count(); $i < $minBreaks; $i++) {
            $breaks->push(new \App\Models\BreakTime([
                'break_start' => null,
                'break_end' => null,
            ]));
        }

        $attendance->setRelation('breaks', $breaks);

        return view('detail', [
            'user' => $user,
            'attendance' => $attendance,
            'id' => $user->id,
            'approval' => $pendingApproval,
            'breaks' => $breaks
        ]);
    }

    abort(403);
}

    public function update(DetailRequest $request, $id) {
        if (!auth('admin')->check()) {
            abort(403);
        }

        if ($id) {
        $attendance = Attendance::find($id);
        }

        if (empty($attendance)) {
            $attendance = Attendance::create([
                'user_id' => $request->user_id,
                'work_date' => $request->work_date,
                'status' => 'off',
            ]);
        }

        // Attendance の基本情報を更新
        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remarks' => $request->remarks,
        ]);

        // 既存 BreakTime を削除して再作成
        $attendance->breaks()->delete();
        foreach ($request->breaks ?? [] as $break) {
            if (!empty($break['start']) || !empty($break['end'])) {
                $attendance->breaks()->create([
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        }

        // 最低2枠の BreakTime を保証（更新後のビュー用）
        $breaks = $attendance->breaks;
        $minBreaks = 2;
        for ($i = $breaks->count(); $i < $minBreaks; $i++) {
            $breaks->push(new \App\Models\BreakTime([
                'break_start' => null,
                'break_end' => null,
            ]));
        }

        $attendance->setRelation('breaks', $breaks);

        return redirect()->route('admin.list.now', ['id'=> $attendance->id]);
    }
}
