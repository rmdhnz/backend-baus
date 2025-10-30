<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Faker\Factory as Faker;

class SendFakeOrder extends Command
{
    /**
     * Nama command (yang akan dijalankan di terminal)
     */
    protected $signature = 'send:fake-order {--count=1 : Jumlah order yang ingin dikirim}';

    /**
     * Deskripsi command
     */
    protected $description = 'Mengirim data order palsu (faker) ke endpoint allocateOrderToStaffIM';

    public function handle()
    {
        $faker = Faker::create('id_ID');
        $count = (int)$this->option('count');

        $baseUrl = "http://app/api/v1/allocate-order-to-staff-im";
        $apiKey = env('BAUS_API_KEY');

        $this->info("Mengirim $count fake order ke: $baseUrl");

        for ($i = 1; $i <= $count; $i++) {
            $payload = $this->generateOrderData($faker);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $apiKey,
            ])->post($baseUrl, $payload);

            if ($response->successful()) {
                $this->info("✅ [$i] Order terkirim: " . $payload['order_no']);
            } else {
                $this->error("❌ [$i] Gagal mengirim: " . $response->body());
            }

            sleep(1); // jeda kecil antar request (opsional)
        }

        return Command::SUCCESS;
    }

    private function generateOrderData($faker)
    {
        $productSamples = [
            ['name' => 'MCD Vodka Mix 1L', 'price' => 128000],
            ['name' => 'Draft Beer 220ml', 'price' => 30000],
            ['name' => 'Chivas Regal 500ml', 'price' => 215000],
            ['name' => 'Guinness Stout 330ml', 'price' => 38000],
            ['name' => 'Soju Green Grape', 'price' => 95000],
            ['name' => 'Soju Strawberry', 'price' => 95000],
            ['name' => 'Red Wine Merlot 750ml', 'price' => 255000],
        ];

        $numItems = rand(1, 3);
        $items = [];

        for ($i = 0; $i < $numItems; $i++) {
            $p = $faker->randomElement($productSamples);
            $quantity = rand(1, 3);
            $price = $p['price'];
            $discount = round($price * (rand(0, 15) / 100), 2);
            $total = ($price - $discount) * $quantity;

            $items[] = [
                'id' => $faker->unique()->numerify('######'),
                'name' => $p['name'],
                'price' => $price,
                'total' => $total,
                'variant' => strtoupper($faker->lexify('??')),
                'combo_id' => $faker->boolean ? $faker->numerify('######') : null,
                'discount' => (string)$discount,
                'quantity' => $quantity,
                'bundle_id' => null,
                'prodvar_id' => $faker->numerify('#####|#####'),
                'variant_id' => $faker->numerify('######'),
                'product_type_id' => rand(1, 3)
            ];
        }

        return [
            'order_id' => (string)$faker->numerify('########'),
            'order_no' => strtoupper($faker->bothify('OL-####??')),
            'cust_name' => $faker->name(),
            'receipt_link' => $faker->url(),
            'items' => $items,
            'subtotal' => $faker->numberBetween(50000, 300000),
            'shipping_fee' => $faker->numberBetween(5000, 15000),
            'discount_amount' => $faker->numberBetween(0, 20000),
            'distance_km' => $faker->randomFloat(2, 0.1, 5.0),
            'delivery_lat' => $faker->latitude(-7.0, -6.8),
            'delivery_lon' => $faker->longitude(110.3, 110.5),
            'delivery_link' => $faker->url(),
            'payment_type_id' => rand(1, 3),
            'notes' => $faker->sentence(),
            'delivery_id' => $faker->numberBetween(1,3),
            'estimated_time_delivered' => $faker->dateTimeBetween('+15 minutes', '+2 hours')->format('Y-m-d H:i:s'),
        ];
    }
}
