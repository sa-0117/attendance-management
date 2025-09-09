<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
            [
                'name' => '秋田 明美',
                'email' => 'tomomi.a@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'email_verified_at' => null,
                'password' => Hash::make('password'),
            ],
        ]);

        //一般ユーザー
        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('password123'),
        ]);

        //test環境のみ
        if (app()->environment('testing')) {
            $testUsers = [
                [
                    'name' => '勤務外ユーザー',
                    'email' => 'off@example.com',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('password123'),
                ],
                [
                    'name' => '出勤中ユーザー',
                    'email' => 'working@example.com',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('password123'),
                ],
                [
                    'name' => '休憩中ユーザー',
                    'email' => 'break@example.com',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('password123'),
                ],
                [
                    'name' => '退勤済みユーザー',
                    'email' => 'end@example.com',
                    'email_verified_at' => Carbon::now(),
                    'password' => Hash::make('password123'),
                ],
            ];

            foreach ($testUsers as $userData) {
                User::create($userData);
            }
        }
    }
}
