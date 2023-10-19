<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert(['name' => 'admin', 'password' => bcrypt('123123')]);
        DB::table('roles')->insert(['name' => 'admin']);
        DB::table('role_user')->insert(['user_id' => 1, 'role_id' => 1]);
    }
}
