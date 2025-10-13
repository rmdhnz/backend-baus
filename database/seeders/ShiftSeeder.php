<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '08:00:00',
                'end_time'   => '16:00:00',
            ],
            [
                'name' => 'Siang',
                'start_time' => '15:00:00',
                'end_time'   => '23:00:00',
            ],
            [
                'name' => 'Malam',
                'start_time' => '23:00:00',
                'end_time'   => '08:00:00',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create([
                'name'        => $shift['name'],
                'start_time'  => $shift['start_time'],
                'end_time'    => $shift['end_time'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
        $this->command?->info('Shift seeding selesai!');
    }
}
