<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\DetailRequest;
use App\Models\Attendance;
use App\Models\Approval;
use App\Models\BreakTime;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    public function requestList(Request $request) {

        $tab = $request->query('tab', 'pending');

        if (auth('admin')->check()) {
            $query = Approval::with(['attendance', 'user']); // すべてのユーザーの申請を表示
        } elseif (auth('web')->check()) {
            $userId = auth('web')->id();
            $query = Approval::with('attendance')->where('user_id', $userId); // 一般用
        } else {
            abort(403);
        }

        if ($tab === 'pending') {
            $query->where('status', 'pending');
        } elseif ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $query->whereRaw('0 = 1');
        }

        $approvals = $query->orderBy('created_at', 'desc')->get();

        if (auth('admin')->check()) {
            return view('admin_request', [
                'approvals' => $approvals,
                'tab' => $tab,
            ]);
        } else {
            return view('request', [
                'approvals' => $approvals,
                'tab' => $tab
            ]);
        }
    }

    public function showApprovalForm($id)
    {
        $approval = Approval::with(['attendance', 'attendance.user'])->findOrFail($id);

         $breaks = is_array($approval->breaks) ? $approval->breaks : [];

        $minBreaks = 2;
        if (count($breaks) < $minBreaks) {
            for ($i = count($breaks); $i < $minBreaks; $i++) {
                $breaks[] = ['start' => null, 'end' => null];
            }
        }

        return view('approvals', compact('approval','breaks'));
    }

    public function approve(Request $request, $id)
    {
        $approval = Approval::findOrFail($id);
        $attendance = Attendance::findOrFail($approval->attendance_id);

        // 勤怠修正
        $attendance->update([
            'clock_in'  => $approval->clock_in,
            'clock_out' => $approval->clock_out,
            'remarks'   => $approval->remarks,
        ]);


        // 休憩差し替え
        $attendance->breaks()->delete();

        $breaks = is_array($approval->breaks) ? $approval->breaks : [];

        foreach ($breaks as $break) {
            $attendance->breaks()->create([
                'break_start' => $break['start'] ?? null,
                'break_end'   => $break['end'] ?? null,
            ]);
        }

        // 承認済みに更新
        $approval->update(['status' => 'approved']);

        return redirect()->route('request.list', ['tab' => 'approved']);
    }

    public function storeRequest(DetailRequest $request, $id)
    { 
        $attendance = Attendance::findOrFail($id);

        $breaks = collect($request->breaks ?? [])
            ->filter(fn($b) => !empty($b['start']) || !empty($b['end']))
            ->values()
            ->all();

        $approval = Approval::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'breaks' => $breaks,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        return redirect()->route('request.list', ['tab' => 'pending']);
    }

}