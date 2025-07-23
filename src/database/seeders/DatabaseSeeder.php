<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminsTableSeeder::class);
    }
}
