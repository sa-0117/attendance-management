<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (app()->environment('test')) {
            // テスト環境
            $this->call([
                UsersTableSeeder::class,
                AdminsTableSeeder::class,
                TestAttendancesTableSeeder::class,
            ]);
        } else {
            // 本番・ローカル環境
            $this->call([
                UsersTableSeeder::class,
                AdminsTableSeeder::class,
            ]);
        }
    }
}
