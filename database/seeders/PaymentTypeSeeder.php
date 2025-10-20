<?php

namespace Database\Seeders;

use App\Models\PaymentType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = ["QRIS","Cash","VA"];
        foreach($orders as $order) { 
            PaymentType::create([
                "name"=> $order,
            ]);
        }
        $this->command->info("Seeding Payment type successful!");
    }
}
