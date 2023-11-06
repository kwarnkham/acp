<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Role;
use App\Models\User;
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
        DB::table('role_user')->insert([
            'user_id' => User::first(['id'])->id,
            'role_id' => Role::first(['id'])->id
        ]);
        DB::table('preferences')->insert(['ticket_expiration' => 60]);
    }
}
