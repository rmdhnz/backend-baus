<?php

namespace Database\Seeders;

use App\Models\DriverCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DriverConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conds = ["OFF","STAY","JALAN"];
        foreach($conds as $cond) { 
            $data = DriverCondition::create([
                "name" => $cond,
            ]);
        }
        $this->command->info("Berhasil mengisi data driver conditions");
    }
}
