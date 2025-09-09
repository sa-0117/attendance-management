<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Admin;
use App\Models\Approval;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class AdminTest extends TestCase
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

    //勤怠一覧情報取得機能
    public function test_admin_can_all_attendances_for_today()
    {
        $userAdmin   = Admin::firstWhere('email', 'testadmin@example.com');

        $today = Carbon::now()->toDateString();

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now', ['date' => $today]));

        $response->assertStatus(200);

        $attendances = Attendance::whereDate('work_date', $today)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }

            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
            }
        }
    }

    public function test_current_date_admin_attendance_list()
    {
        $userAdmin   = Admin::firstWhere('email', 'testadmin@example.com');

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $currentDate = now()->format('Y/m/d');

        $response->assertSee($currentDate);
    }

    public function test_previous_date_admin_attendance_list()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $currentDate = now();

        //URLパラメータ用
        $prevDateParam = $currentDate->copy()->subDay()->toDateString();
        //blade上の表示用
        $prevDateTitle = $currentDate->copy()->subDay()->format('Y年n月j日');
        $prevDateInline = $currentDate->copy()->subDay()->format('Y/m/d'); 

        $response = $this->actingAs($userAdmin, 'admin')->get(route('attendance.admin.list', ['date' => $prevDateParam]));

        $response->assertSee($prevDateTitle); 
        $response->assertSee($prevDateInline); 
        $attendances = Attendance::whereDate('work_date', $prevDateParam)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }

            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
            }
        }       
    }

    public function test_next_date_admin_attendance_list()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $currentDate = now();

        //URLパラメータ用
        $nextDateParam = $currentDate->copy()->subDay()->toDateString();
        //blade上の表示用
        $nextDate = $currentDate->copy()->subDay()->format('Y/m/d'); 

        $response = $this->actingAs($userAdmin, 'admin')->get(route('attendance.admin.list', ['date' => $nextDateParam]));

        $response->assertSee($nextDate); 
        $attendances = Attendance::whereDate('work_date', $nextDateParam)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }

            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
            }
        }       
    }

    //勤怠詳細情報取得・修正機能
    public function test_admin_attendance_details_transition()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        
        //勤怠詳細画面にアクセス
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );
        $response->assertStatus(200);

        $response->assertSee('一般ユーザー')
                 ->assertSee(Carbon::parse($attendance->work_date)->format('Y-m-d'))
                 ->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'))
                 ->assertSee(Carbon::parse($attendance->clock_out)->format('H:i'));

        foreach ($attendance->breaks as $index => $break) {
            $breakLabel = $index === 0 ? '休憩' : '休憩' . ($index + 1);

            $response->assertSee($breakLabel)
                    ->assertSee(Carbon::parse($break->break_start)->format('H:i'))
                    ->assertSee(Carbon::parse($break->break_end)->format('H:i'));
        }
    }
    
    public function test_start_time_later_than_end_time_returns_error_for_admin()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        
        //勤怠詳細画面にアクセス
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userAdmin)->post(
            route('attendance.update', ['id' => $attendance->id]),
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

        public function test_start_break_time_later_than_end_time_returns_error_for_admin()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        
        //勤怠詳細画面にアクセス
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userAdmin)->post(
            route('attendance.update', ['id' => $attendance->id]),
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

    public function test_end_break_time_later_than_end_time_returns_error_for_admin()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        
        //勤怠詳細画面にアクセス
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userAdmin)->post(
            route('attendance.update', ['id' => $attendance->id]),
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

    public function test_remarks_not_entered_returns_error_for_admin()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $this->actingAs($userAdmin, 'admin')->get(route('admin.list.now'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
        
        //勤怠詳細画面にアクセス
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('attendance.detail',[
                'id' => $attendance->id,
                'date' => $attendance->work_date,
            ])
        );

        $response = $this->actingAs($userAdmin)->post(
            route('attendance.update', ['id' => $attendance->id]),
            ['remarks' => '']
        );

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }

    //ユーザー情報取得機能
    public function test_admin_can_see_all_staff()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $users = User::all();

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.staff.list'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_admin_can_see_staff_list()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.staff.list'));

        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();
                
        $response = $this->actingAs($userAdmin, 'admin')->get(
            route('admin.attendance.staff',[
                'id' => $userGeneral->id,
                'date' => $attendance->work_date,
            ])
        );

        $response->assertStatus(200);
        $response->assertSee($userGeneral->name);
        $response->assertSee($attendance->work_date->format('Y-m'));
    }

        public function test_previous_month_admin_staff_list()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');
        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $this->actingAs($userAdmin, 'admin')->get(route('admin.staff.list'));
        

        $currentMonth = now();
        $prevDateParam = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $prevMonth = now()->subMonth()->format('Y/m');


        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.attendance.staff', ['id' => $userGeneral->id, 'date' => $prevDateParam]));

        $response->assertSee($prevMonth);
        
        $attendances = Attendance::whereDate('work_date', $prevDateParam)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }

            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
            }
        }       
    }

     public function test_next_month_admin_staff_list()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');
        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)->firstOrFail();

        $this->actingAs($userAdmin, 'admin')->get(route('admin.staff.list'));
        
        $currentMonth = now();
        $nextDateParam = now()->addMonth()->startOfMonth()->format('Y-m-d');
        $nextMonth = now()->addMonth()->format('Y/m');


        $response = $this->actingAs($userAdmin, 'admin')->get(route('admin.attendance.staff', ['id' => $userGeneral->id, 'date' => $nextDateParam]));

        $response->assertSee($nextMonth);
        
        $attendances = Attendance::whereDate('work_date', $nextDateParam)->get();
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->user->name);
            if ($attendance->clock_in) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_in)->format('H:i'));
            }

            if ($attendance->clock_out) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->clock_out)->format('H:i'));
            }
        }       
    }

    public function test_attendance_details_screen()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');
        $userGeneral = User::firstwhere('email', 'user@example.com');

        $attendance = Attendance::where('user_id', $userGeneral->id)
            ->where('work_date', now()->toDateString())
            ->firstOrFail();

        $this->actingAs($userAdmin, 'admin');

        // 勤怠一覧ページ
        $response = $this->get(route('admin.attendance.staff', ['id' => $userGeneral->id]));
        $response->assertStatus(200)
                 ->assertSee($userGeneral->name);

        // 詳細ページ
        $response = $this->get(route('attendance.detail', [
            'id' => $attendance->id,
            'staff_id' => $userGeneral->id,
            'date' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee($userGeneral->name);
        $response->assertViewIs('admin_detail');
    }

    //勤怠情報修正機能
     public function test_admin_can_see_pending_approval_requests()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $users = User::all();

        foreach ($users as $user) {
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => now()->toDateString(),
                'clock_in' => now()->subHours(8),
                'clock_out' => now(),
                'status' => 'end',
            ]);
        }

            Approval::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => $attendance->clock_in ?? now()->subHours(8),
                'clock_out' => $attendance->clock_out ?? now(),
                'breaks' => [],
                'remarks' => 'テスト',
                'status' => 'pending',
            ]);
        }

        $this->actingAs($userAdmin, 'admin');

        $response = $this->get(route('request.list', ['tab' => 'pending']));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }
        $response->assertViewIs('admin_request');
    }

    public function test_admin_can_view_approved_approvals()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');

        $users = User::all();

        foreach ($users as $user) {
        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => now()->toDateString(),
            ],
            [
                'clock_in' => now()->subHours(8),
                'clock_out' => now(),
                'status' => 'end',
            ]
        );

            Approval::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'clock_in' => $attendance->clock_in ?? now()->subHours(8),
                'clock_out' => $attendance->clock_out ?? now(),
                'breaks' => [],
                'remarks' => 'テスト',
                'status' => 'pending',
            ]);
        }

        Approval::query()->update(['status' => 'approved']);

        $this->actingAs($userAdmin, 'admin');

        $response = $this->get(route('request.list', ['tab' => 'approved']));
        $response->assertStatus(200);

        foreach (User::all() as $user) {
            $response->assertSee($user->name);
        }
    }

    public function test_admin_can_view_approval_details()
    {
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');
        $this->actingAs($userAdmin, 'admin');

        $userGeneral = User::firstWhere('email', 'user@example.com');
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userGeneral->id, 'work_date' => now()->toDateString()],
            ['clock_in' => now()->subHours(8), 'clock_out' => now(), 'status' => 'end']
        );

        $approval = Approval::create([
            'attendance_id' => $attendance->id,
            'user_id' => $userGeneral->id,
            'clock_in' => $attendance->clock_in,
            'clock_out' => $attendance->clock_out,
            'breaks' => [
                ['start' => now()->subHours(6), 'end' => now()->subHours(5)],
                ['start' => now()->subHours(3), 'end' => now()->subHours(2, 30)],
            ],
            'remarks' => 'テスト',
            'status' => 'pending',
        ]);

        $response = $this->get(route('approval.show', ['attendance_correct_request' => $approval->id]));

        $response->assertSee($userGeneral->name);
        $response->assertSee($attendance->work_date->format('Y-m-d'));
        $response->assertSee($approval->clock_in->format('H:i'));
        $response->assertSee($approval->clock_out->format('H:i'));
        $response->assertSee('テスト');

        foreach ($approval->breaks as $break) {
            if (!empty($break['start'])) {
                $response->assertSee(\Carbon\Carbon::parse($break['start'])->format('H:i'));
            }
            if (!empty($break['end'])) {
                $response->assertSee(\Carbon\Carbon::parse($break['end'])->format('H:i'));
            }
        }
    }

    public function test_admin_can_approve_a_request()
    {
        // 管理者ログイン
        $userAdmin = Admin::firstWhere('email', 'testadmin@example.com');
        $this->actingAs($userAdmin, 'admin');

        $userGeneral = User::firstWhere('email', 'user@example.com');
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userGeneral->id, 'work_date' => now()->toDateString()],
            ['clock_in' => now()->subHours(8), 'clock_out' => now()->subHours(1), 'status' => 'end']
        );

        $approval = Approval::create([
            'attendance_id' => $attendance->id,
            'user_id' => $userGeneral->id,
            'clock_in' => now()->subHours(7),
            'clock_out' => now()->subHours(2),
            'breaks' => [
                ['start' => now()->subHours(5)->format('H:i'),
                'end' => now()->subHours(4)->format('H:i'),
                ],
                ['start' => now()->subHours(3)->format('H:i'),
                'end' => now()->subHours(2, 30)->format('H:i'),
                ],
            ],
            'remarks' => 'テスト承認',
            'status' => 'pending',
        ]);

        //承認処理
        $response = $this->post(route('approval.approve', ['attendance_correct_request' => $approval->id]));

        $response->assertRedirect(route('request.list', ['tab' => 'approved']));

        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'approved',
        ]);

        //勤怠情報が更新されていることを確認
        $updatedAttendance = Attendance::find($attendance->id);
        $this->assertEquals($approval->clock_in->format('H:i'), $updatedAttendance->clock_in->format('H:i'));
        $this->assertEquals($approval->clock_out->format('H:i'), $updatedAttendance->clock_out->format('H:i'));

        //休憩情報の更新を確認
        $updatedBreaks = $updatedAttendance->breaks()->get()->toArray();
        $this->assertCount(2, $updatedBreaks);

        $this->assertEquals(
            \Carbon\Carbon::parse($approval->breaks[0]['start'])->format('H:i'),
            \Carbon\Carbon::parse($updatedBreaks[0]['break_start'])->format('H:i')
        );
        $this->assertEquals(
            \Carbon\Carbon::parse($approval->breaks[0]['end'])->format('H:i'),
            \Carbon\Carbon::parse($updatedBreaks[0]['break_end'])->format('H:i')
        );
    }


}