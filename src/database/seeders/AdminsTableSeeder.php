<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admins')->insert([
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
        ]);

        if (app()->environment('test')) {
            Admin::create([
                'email' => 'testadmin@example.com',
                'password' => Hash::make('password123'),
            ]);
        }
    }
}
