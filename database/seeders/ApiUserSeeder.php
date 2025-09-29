<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\ApiUser;

class ApiUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if the user already exists to avoid duplicates
        if (!ApiUser::where('username', 'bankcash')->exists()) {
            ApiUser::create([
                'username' => 'bankcash',
                'password' => Hash::make('B@nkcash!1510++'),
            ]);
        }
    }
}