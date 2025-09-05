<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MailTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "aaa@example.com",
            'password' => "password123",
            'password_confirmation' => "password123",
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user = User::where('email', 'aaa@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);

    }

    public function test_go_to_email_verification_notice_page()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "aaa@example.com",
            'password' => "password123",
            'password_confirmation' => "password123",
        ]);

        $user = User::where('email', 'aaa@example.com')->first();

        $response = $this->actingAs($user)->get(route('verification.notice'));
        $response->assertSee('認証はこちらから');

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');
    }

    public function test_email_verification_redirects_to_attendance_page()
    {
        $response = $this->post('/register', [
            'name' => "テストユーザー",
            'email' => "aaa@example.com",
            'password' => "password123",
            'password_confirmation' => "password123",
        ]);

        $user = User::where('email', 'aaa@example.com')->first();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);
        $response->assertRedirect('/attendance');
    }
}
