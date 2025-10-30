<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses= ["In Packing","Ready to Ship","Arrived","On The Way","Delivered","Pending","Cancelled"];
        foreach($statuses as $status) { 
            OrderStatus::create([
                "name" => $status
            ]);
        }
        $this->command->info("Berhasil menambahkan data status order");
    }
}
