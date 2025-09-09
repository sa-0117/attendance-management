<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\Approval;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_date_and_time()
    {
        $user = User::firstWhere('email', 'user@example.com');

        $user->forceFill(['email_verified_at' => now()])->save();
        $user = $user->fresh();

        $today = now()->format('Y年n月j日');
        $dayOfWeek = ['日','月','火','水','木','金','土'][now()->dayOfWeek];

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee("{$today}（{$dayOfWeek}）");
    }

    public function test_off_user_status()
    {
        $userOff = User::firstWhere('email', 'off@example.com');

        $response = $this->actingAs($userOff)->get('/attendance');
        $response->assertSee('勤務外');
    }

    public function test_working_user_status()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_break_user_status()
    {
        $userBreak = User::firstWhere('email', 'break@example.com');

        $response = $this->actingAs($userBreak)->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_end_user_status()
    {
        $userEnd = User::firstWhere('email', 'end@example.com');

        $response = $this->actingAs($userEnd)->get('/attendance');
        $response->assertSee('退勤済');
    }

    //出勤機能
    public function test_off_user_can_start_work()
    {
        Date::setTestNow('2025-01-01 09:00');

        $userOff = User::firstWhere('email', 'off@example.com');

        $response = $this->actingAs($userOff)->get('/attendance');
        $response->assertSee('出勤');

        //出勤処理
        $response = $this->actingAs($userOff)->post(route('attendance.start'));
 
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userOff)->get(route('attendance.form'));
        $response->assertSee('出勤中');
    }

    public function test_cannot_start_work_twice()
    {
        $userEnd = User::firstWhere('email', 'end@example.com');

        $response = $this->actingAs($userEnd)->get('/attendance');
        $response->assertDontSee('出勤');
    }

    public function test_clock_in_shows_in_attendance_list()
    {
        $userOff = User::firstWhere('email', 'off@example.com');

        $this->actingAs($userOff)->post('/attendance');

        $response = $this->actingAs($userOff)->get('/attendance/list');

        $today = now()->format('Y-m-d');
        $response->assertSee($today);
    }

    //休憩機能
    public function test_off_user_can_break()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        $response->assertSee('休憩中');
    }

    public function test_can_break_time_repeat()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->get('/attendance');
        $response->assertSee('休憩入');

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response->assertRedirect(route('attendance.form'));

        $response = $this->actingAs($userWorking)->get(route('attendance.form'));

        $response = $this->actingAs($userWorking)->post(route('break.end'));
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        $response->assertSee('休憩入');
    }

    public function test_break_user_can_break_end()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->get('/attendance');

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->post(route('break.end'));
        $response->assertRedirect(route('attendance.form'));

        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        $response->assertSee('出勤中');
    }

    public function test_can_break_end_repeat()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->get('/attendance');

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response->assertRedirect(route('attendance.form'));

        $response = $this->actingAs($userWorking)->get(route('attendance.form'));

        $response = $this->actingAs($userWorking)->post(route('break.end'));
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->get(route('attendance.form'));

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response->assertRedirect(route('attendance.form'));

        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        $response->assertSee('休憩戻');
    }

    public function test_break_time_shows_in_attendance_list()
    {
        $userWorking = User::firstWhere('email', 'working@example.com');

        $response = $this->actingAs($userWorking)->post(route('break.start'));
        $response = $this->actingAs($userWorking)->post(route('break.end'));

        //DBから休憩データを取得
        $attendance = Attendance::where('user_id', $userWorking->id)
            ->whereDate('work_date', now()->toDateString())
            ->latest()
            ->first();

        $break = $attendance->breaks()->latest()->first();

        //勤怠一覧画面
        $response = $this->actingAs($userWorking)->get('/attendance/list');

        //休憩の日付取得
        $attendanceDate = $attendance->work_date->format('m/d');
        $response->assertSee($attendanceDate);
    }

    //退勤機能
    public function test_working_user_can_end_work()
    {
        Date::setTestNow('2025-01-01 18:00');

        $userWorking = User::firstWhere('email', 'working@example.com');

        Attendance::updateOrCreate(
            ['user_id' => $userWorking->id, 'work_date' => now()->toDateString()],
            [
                'clock_in' => now()->subHours(2),
                'status' => 'working',
            ]
        );

        $response = $this->actingAs($userWorking)->get('/attendance');
        $response->assertSee('退勤');

        $response = $this->actingAs($userWorking)->post(route('attendance.end'));
        $response->assertRedirect(route('attendance.form'));
        
        $response = $this->actingAs($userWorking)->get(route('attendance.form'));
        $response->assertSee('退勤済');
    }

    public function test_clock_out_shows_in_attendance_list()
    {
        $userOff = User::firstWhere('email', 'off@example.com');

        $this->actingAs($userOff)->post('/attendance');

        $response = $this->actingAs($userOff)->post(route('attendance.start'));
        $response = $this->actingAs($userOff)->post(route('attendance.end'));

        $response = $this->actingAs($userOff)->get('/attendance/list');

        $today = now()->format('m/d');
        $response->assertSee($today);
    }

    //勤怠一覧情報取得機能
    public function test_view_attendance_infomation()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();

        //勤怠登録
        $attendance = Attendance::create([
            'user_id' => $userGeneral->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->copy()->subHours(8),
            'clock_out' => now()->copy()->subHours(1), 
        ]);

        $attendance->breaks()->create([
            'break_start' => now()->copy()->subHours(7),
            'break_end' => now()->copy()->subHours(6),
        ]);

        $response = $this->actingAs($userGeneral)->get('/attendance/list');

        $response->assertSee(now()->format('m/d'));
        $response->assertSee(now()->copy()->subHours(8)->format('H:i')); 
        $response->assertSee(now()->copy()->subHours(1)->format('H:i'));
        $response->assertSee('01:00'); 
        $response->assertSee('06:00');
        
    }


    public function test_current_month_attendance_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $response = $this->actingAs($userGeneral)->get('/attendance/list');

        $currentMonth = now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    public function test_previous_month_attendance_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $response = $this->actingAs($userGeneral)->get('/attendance/list');

        $currentMonth = now();

        //URLパラメータ用
        $prevDateParam = $currentMonth->copy()->subMonth()->toDateString();
        //blade上の表示用
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y/m');

        $response = $this->actingAs($userGeneral)->get(route('attendance.list', ['date' => $prevDateParam]));

        $response->assertSee($prevMonth);
    }

        public function test_next_month_attendance_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $response = $this->actingAs($userGeneral)->get('/attendance/list');

        $currentMonth = now();

        //URLパラメータ用
        $nextDateParam = $currentMonth->copy()->addMonth()->toDateString();
        //blade上の表示用
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y/m');

        $response = $this->actingAs($userGeneral)->get(route('attendance.list', ['date' => $nextDateParam]));

        $response->assertSee($nextMonth);
    }

    public function test_attendance_details_transition()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $response = $this->actingAs($userGeneral)->get('/attendance/list');

        //勤怠データ取得
        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        //勤怠詳細画面にアクセス
        $responce = $this->actingAs($userGeneral)->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertStatus(200);
    }

    //勤怠詳細情報取得機能
    public function test_name_shows_in_attendance_detail_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertSee($userGeneral->name); 
    }

        public function test_date_shows_in_attendance_detail_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertSee($attendance->work_date); 
    }

    public function test_clock_in_clock_out_shows_in_attendance_detail_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = \App\Models\Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertSee($attendance->work_date); 
        if ($attendance->clock_in) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
        }
        if ($attendance->clock_out) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
        }
    }

    public function test_breaks_shows_in_attendance_detail_list()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertSee($attendance->work_date); 
        if ($attendance->break_start) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->break_start)->format('H:i'));
        }
        if ($attendance->break_end) {
            $response->assertSee(\Carbon\Carbon::parse($attendance->break_end)->format('H:i'));
        }
    }

    //勤怠詳細情報修正機能
    public function test_start_time_later_than_end_time_returns_error()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = \App\Models\Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userGeneral)->post(
            route('attendance.request', ['id' => $attendance->id]),
            [
                'work_date' => now()->format('Y-m-d'),
                'clock_in' => '18:00',
                'clock_out' => '09:00',
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

        public function test_start_break_time_later_than_end_time_returns_error()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = \App\Models\Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userGeneral)->post(
            route('attendance.request', ['id' => $attendance->id]),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '20:00'], 
                ],
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_end_break_time_later_than_end_time_returns_error()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userGeneral)->post(
            route('attendance.request', ['id' => $attendance->id]),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '17:30', 'end' => '18:30'], 
                ],
            ]
        );
        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_remarks_not_entered_returns_error()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id'   => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userGeneral)->post(
            route('attendance.request', ['id' => $attendance->id]),
            ['remarks' => '']
        );

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    public function test_user_can_create_correction_request()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->post(route('attendance.request', [
            'id' => $attendance->id,
        ]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'], 
            ],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('request.list', ['tab' => 'pending']));

        $this->assertDatabaseHas('approvals', [
            'attendance_id' => $attendance->id,
            'user_id' => $userGeneral->id,
            'status' => 'pending',
            'remarks' => 'テスト',
        ]);

        //管理者ユーザーで承認画面を確認
        $userAdmin = Admin::firstwhere('email', 'testadmin@example.com');

        $approval = Approval::where('status', 'pending')->firstOrFail();

        $response = $this->actingAs($userAdmin, 'admin')
            ->get(route('approval.show', ['attendance_correct_request' => $approval->id]))
            ->assertStatus(200)
            ->assertSee($approval->remarks);


        //申請一覧画面を確認
        $this->actingAs($userAdmin, 'admin')
            ->get(route('request.list', ['tab' => 'pending']))
            ->assertStatus(200)
            ->assertSee($approval->remarks);
    }

    public function test_request_are_displayed_in_pending()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $this->actingAs($userGeneral)->get('/attendance/list');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->get(
            route('attendance.detail', [
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->post(route('attendance.request', [
            'id' => $attendance->id,
        ]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'], 
            ],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('request.list', ['tab' => 'pending']));

        $this->assertDatabaseHas('approvals', [
            'attendance_id' => $attendance->id,
            'user_id' => $userGeneral->id,
            'status' => 'pending',
            'remarks' => 'テスト',
        ]);

        $approval = Approval::where('attendance_id', $attendance->id)
            ->where('user_id', $userGeneral->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $this->actingAs($userGeneral)
            ->get(route('request.list', ['tab' => 'pending']))
            ->assertStatus(200)
            ->assertSee($approval->remarks);
            
    }

    public function test_request_are_displayed_in_approved()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();


        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->post(route('attendance.request', ['id' => $attendance->id,]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'], 
            ],
            'remarks' => 'テスト',
        ]);

        $approval = Approval::where('attendance_id', $attendance->id)
            ->where('user_id', $userGeneral->id)
            ->where('status', 'pending')
            ->firstOrFail();

        //管理者で承認
        $userAdmin   = Admin::firstWhere('email', 'testadmin@example.com');
        $this->actingAs($userAdmin, 'admin')
            ->followingRedirects()
            ->post(route('approval.approve', ['attendance_correct_request' => $approval->id]));

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);

        //一般ユーザーで承認済みの修正申請を確認
        $this->actingAs($userGeneral)
            ->get(route('request.list', ['tab' => 'approved']))
            ->assertStatus(200)
            ->assertSee('テスト');
    }

    public function test_move_to_attendance_details_screen()
    {
        $userGeneral = User::firstWhere('email', 'user@example.com');

        $userGeneral->forceFill(['email_verified_at' => now()])->save();
        $userGeneral = $userGeneral->fresh();

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $response = $this->actingAs($userGeneral)->post(route('attendance.request', ['id' => $attendance->id,]),
        [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'], 
            ],
            'remarks' => 'テスト',
        ]);

        $response->assertRedirect(route('request.list', ['tab' => 'pending']));

        //申請一覧画面にアクセス
        $response = $this->actingAs($userGeneral)
            ->get(route('request.list', ['tab' => 'pending']))
            ->assertStatus(200)
            ->assertSee('テスト'); 

        //「詳細」ボタン押下 → 勤怠詳細画面に遷移
        $response = $this->actingAs($userGeneral)
            ->get(route('attendance.detail', [
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ]))
            ->assertStatus(200)
            ->assertSee('テスト');
    }
}


