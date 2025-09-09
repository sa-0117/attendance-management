<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_admin()
    {   
        $admin = Admin::firstWhere('email', 'testadmin@example.com');

        $response = $this->withSession(['url.intended' => '/admin/attendance/list'])->post('/admin/login',[    
            'email'=> "testadmin@example.com",
            'password' => "adminpassword",
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_admin_validate_email()
    {
        $response = $this->post('/admin/login', [
            'email' => "",
            'password' => "adminpassword",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('メールアドレスを入力してください', $errors->first('email'));
    }

    public function test_login_admin_validate_password()
    {
        $response = $this->post('/admin/login', [
            'email' => "testadmin@example.com",
            'password' => "",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');

        $errors = session('errors');
        $this->assertEquals('パスワードを入力してください', $errors->first('password'));
    }

    public function test_login_user_validate_user()
    {
        $response = $this->post('/admin/login', [
            'email' => "test@example.com",
            'password' => "adminpassword",
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');

        $errors = session('errors');
        $this->assertEquals('ログイン情報が登録されていません。', $errors->first('email'));
    }
}
