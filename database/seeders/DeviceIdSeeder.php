<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class DeviceIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereNull('device_id')->orWhere('device_id', '')->get();

        if ($users->isEmpty()) {
            $this->command->info('✅ Semua user sudah memiliki device_id.');
            return;
        }

        foreach ($users as $user) {
            $user->device_id = Str::uuid(); 
            $user->save();

            $this->command->info("📱 Device ID generated for user: {$user->username} ({$user->device_id})");
        }

        $this->command->info("🎉 Device ID seeding completed for {$users->count()} users!");
    }
}
