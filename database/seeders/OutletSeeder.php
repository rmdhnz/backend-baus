<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $outlet = [
            "name" => "Solo",
            "jam_buka" => "10:00",
            "jam_tutup" => "22:00",
            "latitude" =>  -7.56072610,
            "longitude" => 110.84982040,
            "address" => "Jl. Ir. Sutami No.23, Jebres, Kec. Jebres, Kota Surakarta, Jawa Tengah 57126, Indonesia",
            "phone" => "6282146617782",
            "created_at" => now(),
            "updated_at" => null,
        ];
        $outlet = Outlet::create($outlet);
        $this->command->info("Berhasil menambah data outlet");
    }
}
