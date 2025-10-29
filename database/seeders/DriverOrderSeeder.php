<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use Illuminate\Support\Str;

class DriverOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Cek jika sudah ada order, jangan duplikasi
        if (Order::count() > 0) {
            $this->command->info('Orders already exist. Skipping order seeding.');
            return;
        }

        // Buat 20 order dengan lokasi acak di sekitar Jakarta
        for ($i = 0; $i < 20; $i++) {
            Order::create([
                'order_no'         => 'OL' . Str::upper(Str::random(8)),
                'order_id'         => now()->format('YmdHis') . rand(10, 99), 
                'cust_name'        => 'Customer ' . $i,
                // 'driver_id'        => fake()->randomElement([1,2,3,4]),
                'delivery_lat'     => -6.2 + mt_rand(-1000, 1000) / 10000,
                'delivery_lon'     => 106.8 + mt_rand(-1000, 1000) / 10000,
                'subtotal'         => rand(30000, 100000),
                "delivery_id"      => fake()->randomElement([1,2,3]),
                'shipping_fee'     => 0,
                'discount_amount'  => 0,
                'distance_km'      => 0,
                'receipt_link'     => null,
                'notes'            => null,
                'payment_type_id'  => 1, // testing doang
                'status'          => 'PENDING',
            ]);
        }

        $this->command->info('✅ 20 dummy orders created successfully.');
    }
}
