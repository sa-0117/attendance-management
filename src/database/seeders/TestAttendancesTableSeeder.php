<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class TestAttendancesTableSeeder extends Seeder
{
    public function run()
    {
        //test環境でのみ実行
        if (!app()->environment('test')) return;

        $now = Carbon::now();

        $testUsers = [
            'off@example.com'=> ['status' => 'off', 'clock_in_offset' => null, 'clock_out_offset' => null],
            'working@example.com' => ['status' => 'working', 'clock_in_offset' => 2, 'clock_out_offset' => null],
            'break@example.com' => ['status' => 'break', 'clock_in_offset' => 3,'clock_out_offset' => null],
            'end@example.com' => ['status' => 'end', 'clock_in_offset' => 9, 'clock_out_offset' => 1],
            'user@example.com'=> ['status' => 'end', 'clock_in_offset' => 8, 'clock_out_offset' => 1],
        ];

        foreach ($testUsers as $email => $data) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                continue;
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $now->format('Y-m-d'),
                'clock_in' => $data['clock_in_offset'] ? $now->copy()->subHours($data['clock_in_offset']) : null,
                'clock_out' => $data['clock_out_offset'] ? $now->copy()->subHours($data['clock_out_offset']) : null,
                'status' => $data['status'],
            ]);

            // breaksはuser@example.com のみ
            if ($email === 'user@example.com') {
                $attendance->breaks()->createMany([
                    [
                        'break_start' => $now->copy()->subHours(6),
                        'break_end'   => $now->copy()->subHours(5),
                    ],
                    [
                        'break_start' => $now->copy()->subHours(3),
                        'break_end'   => $now->copy()->subHours(2)->subMinutes(30),
                    ],
                ]);

                // 前月と翌月の勤怠も作成
                $prevMonthDate = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                $nextMonthDate = $now->copy()->addMonth()->startOfMonth()->format('Y-m-d');

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $prevMonthDate,
                    'clock_in'=> $now->copy()->subMonth()->endOfMonth()->subHours(8),
                    'clock_out'=> $now->copy()->subMonth()->endOfMonth()->subHours(1),
                    'status'=> 'end',
                ]);

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date'=> $nextMonthDate,
                    'clock_in' => $now->copy()->addMonth()->startOfMonth()->subHours(8),
                    'clock_out' => $now->copy()->addMonth()->endOfMonth()->subHours(1),
                    'status' => 'end',
                ]);
            }
        }
    }
}
