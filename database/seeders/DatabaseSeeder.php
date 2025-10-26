<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(ShiftSeeder::class);
        $this->call(DeliverySeeder::class);
        $this->call(PaymentTypeSeeder::class);
        $this->call(UserDriverSeeder::class);
        $this->call(StaffIMUserSeeder::class);
        $this->call(DeviceIdSeeder::class);
    }
}
