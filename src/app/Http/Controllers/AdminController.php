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

    public function showStaffList() {
        return view('staff.list');
    }

}
