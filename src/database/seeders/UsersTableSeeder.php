<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'password' => Hash::make('reinapassword'),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => Hash::make('taropassword'),            
            ],

            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'password' => Hash::make('isseipassword'),  
            ],

            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => Hash::make('keikichipassword'),  
            ],

            [
                'name' => '秋田 明美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => Hash::make('tomomipassword'),  
            ],

            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => Hash::make('noriopassword'),  
            ],
        ]);
    }
}
