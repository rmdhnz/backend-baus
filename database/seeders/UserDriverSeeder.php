<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Services\DriverService;
use Illuminate\Support\Facades\Hash;

class UserDriverSeeder extends Seeder
{
    private $driverSrv;
    public function __construct (DriverService $driver){
        $this->driverSrv = $driver;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = $this->driverSrv->getAllDrivers();

        if (!isset($data['data']) || !is_array($data['data'])) {
            $this->command?->error('Gagal mengambil data driver dari API eksternal.');
            return;
        }

        foreach ($data['data'] as $driver) {
            // Buat username unik
            $baseUsername = strtolower(preg_replace('/\s+/', '', $driver['name'] ?? 'user'));
            $username = $baseUsername;
            $suffix = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $suffix++;
            }

            $user = User::create([
                'name'      => $driver['name'] ?? 'Unknown',
                'username'  => $username,
                'email'     => null,
                'phone'     => $driver['phone_number'] ?? null,
                'password'  => Hash::make('anjay123'),
                'role_id'   => 2, // role driver
            ]);

            Driver::create([
                'user_id'             => $user->id,
                'shift_id'            => fake()->randomElement([1,2,3]), // ganti dengan shift_id yang sesuai kalau ada
                'productivity_score'  => 0,
                'total_transaction'   => 0,
                'on_time_frequency'   => 0,
                'late_frequency'      => 0,
                'avg_remaining_time'  => null,
                'avg_latest'          => null,
                "status"              => fake()->randomElement(["OFF","STAY","JALAN"])
            ]);
        }

        $this->command?->info('Driver & user seeding selesai!');
    }
}
