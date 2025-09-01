<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class TestAttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $userOff = User::where('email', 'off@example.com')->first();
        Attendance::create([
            'user_id' => $userOff->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => null,
            'clock_out' => null,
            'status' => 'off', 
        ]);

        $userWorking = User::where('email', 'working@example.com')->first();
        Attendance::create([
            'user_id' => $userWorking->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => $now->copy()->subHours(2),
            'clock_out' => null,
            'status' => 'working', 
        ]);

        $userBreak = User::where('email', 'break@example.com')->first();
        Attendance::create([
            'user_id' => $userBreak->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => $now->copy()->subHours(3),
            'clock_out' => null,
            'status' => 'break',
        ]);

        $userEnd = User::where('email', 'end@example.com')->first();
        Attendance::create([
            'user_id' => $userEnd->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in' => $now->copy()->subHours(9),
            'clock_out' => $now->copy()->subHours(1),
            'status' => 'end',
        ]);

        $userGeneral = User::where('email', 'user@example.com')->first();

        //現在の勤怠データ
        $attendance = Attendance::create([
            'user_id'   => $userGeneral->id,
            'work_date' => $now->format('Y-m-d'),
            'clock_in'  => $now->copy()->subHours(8),  
            'clock_out' => $now->copy()->subHours(1), 
            'status'    => 'end',
        ]);

        $attendance->breaks()->create([
            'break_start' => $now->copy()->subHours(6),
            'break_end' => $now->copy()->subHours(5),
        ]);

        $attendance->breaks()->create([
            'break_start' => $now->copy()->subHours(3),
            'break_end' => $now->copy()->subHours(2)->subMinutes(30),
        ]);

        //前月
        $prevMonthDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $attendance = Attendance::create([
            'user_id'   => $userGeneral->id,
            'work_date' => $prevMonthDate,
            'clock_in'  => $now->copy()->subMonth()->endOfMonth()->subHours(8), 
            'clock_out' => $now->copy()->subMonth()->endOfMonth()->subHours(1),
            'status'    => 'end',
        ]);

        //翌月
        $nextMonthDate = Carbon::now()->addMonth()->startOfMonth()->format('Y-m-d');
        $attendance = Attendance::create([
            'user_id'   => $userGeneral->id,
            'work_date' => $nextMonthDate,
            'clock_in'  => $now->copy()->addMonth()->startOfMonth()->subHours(8), 
            'clock_out' => $now->copy()->addMonth()->endOfMonth()->subHours(1),
            'status'    => 'end',
        ]);
    }
}
