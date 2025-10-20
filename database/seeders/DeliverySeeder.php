<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveries = [
            "FD" => "Free Delivery",
            "I" => "Instant Delivery",
            "EX" => "Express Delivery"
        ];
        foreach($deliveries as $alias => $deliv) { 
            Delivery::create([
                "name" => $deliv,
                "alias" => $alias
            ]);
        }
        $this->command->info("Seeding Delivery Type successfully!");
    }
}
