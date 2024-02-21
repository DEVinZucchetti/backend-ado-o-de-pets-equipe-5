<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InitialUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'ADMIN',
            'email' => 'josecdia@hotmail.com',
            'password' => env("DEFAULT_PASSWORD"),
            'profile_id' => 1
        ]);
    }
}
