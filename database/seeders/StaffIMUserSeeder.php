<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Staff_IM;

class StaffIMUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // hapus data lama biar gak bentrok
        Staff_IM::truncate();
        User::where('role_id', 3)->delete();

        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => fake()->name(),
                'username' => 'staffim'.$i,
                'email' => null,
                'phone' => '08123456'.str_pad($i, 4, '0', STR_PAD_LEFT),
                'password' => Hash::make('anjay123'),
                'role_id' => 3, // role staff IM
                'outlet_id' => 1,
            ]);

            Staff_IM::create([
                'user_id' => $user->id,
                'shift_id' => fake()->randomElement([1, 2, 3]),
                'productivity_score' => 0,
                'total_transaction' => 0,
                'on_time_frequency' => 0,
                'late_frequency' => 0,
                'avg_remaining_time' => null,
                'avg_latest' => null,
            ]);
        }
    }

}
