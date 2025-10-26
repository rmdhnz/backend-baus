<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SupervisorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spv = User::create([
            "name" => "Supervisor",
            "username" => "supervisor",
            "email" => "supervisor@gmail.com",
            "phone" => "081377624869",
            "password" => Hash::make("anjay123"),
            "role_id" => 1,
            "active_status" => true,
            "created_at" => now(),
        ]);
        $this->command?->info("Supervisor user created: username='supervisor', password='anjay123'");
    }
}
