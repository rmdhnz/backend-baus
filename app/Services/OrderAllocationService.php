<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\GMapService;
use App\Services\DriverService;
class OrderAllocationService
{
    protected GMapService $gmap;
    protected $driverSvc;

    public function __construct(GMapService $gmap,DriverService $driverSvc)
    {
        $this->gmap = $gmap;
        $this->driverSvc = $driverSvc;
    }

    public function allocateOrdersToDrivers(): void
    {
        Log::info('🚀 [OrderAllocationService] Mulai proses alokasi pesanan...');

        DB::transaction(function () {
            // Ambil order yang belum punya driver dan masih PENDING (order_status_id = 2)
            $orders = Order::whereNull('driver_id')
                ->with('delivery')
                ->get();

            // Ambil driver yang STAY
            $drivers = Driver::where('status', 'STAY')->get();
            // $drivers = collect($this->driverSvc->getDriverInShift());


            if ($orders->isEmpty() || $drivers->isEmpty()) {
                Log::warning('⚠️ Tidak ada order (status_id=2) atau driver STAY.');
                return;
            }

            $maxOrdersPerDriver = 4;
            $maxExpressPerDriver = 2;
            $maxDistance = 1500; // meter sesuai aturan jarak antar order
            $assignedCount = 0;

            // Urutkan order: EX/I lebih dulu
            $sortedOrders = $orders->sortByDesc(function ($o) {
                return in_array($o->delivery->alias, ['EX', 'I']) ? 1 : 0;
            });

            foreach ($drivers as $driver) {
                $driverOrders = [];

                foreach ($sortedOrders as $order) {
                    // Skip kalau sudah teralokasi
                    if ($order->driver_id !== null) continue;

                    // Batas maksimal pesanan per driver
                    if (count($driverOrders) >= $maxOrdersPerDriver) break;

                    // Hitung jumlah EX/I yang sudah ada
                    $expressCount = collect($driverOrders)->filter(function ($o) {
                        return in_array($o['delivery_type'], ['EX', 'I']);
                    })->count();

                    // Kalau sudah ada 2 EX/I, skip EX/I berikutnya
                    if (in_array($order->delivery->alias, ['EX', 'I']) && $expressCount >= $maxExpressPerDriver) {
                        continue;
                    }

                    // === Cek jarak dengan chain <= 300m antar order ===
                    $canAssign = true;
                    if (!empty($driverOrders)) {
                        $lastOrder = end($driverOrders);

                        $distance = $this->gmap->getDistanceInMeters(
                            $lastOrder['delivery_lat'],
                            $lastOrder['delivery_lon'],
                            $order->delivery_lat,
                            $order->delivery_lon
                        );

                        Log::info("📏 [Driver {$driver['user_id']}] jarak {$lastOrder['order_no']} → {$order['order_no']}: {$distance} m");

                        if ($distance === null || $distance > $maxDistance) {
                            $canAssign = false;
                        }
                    }

                    if ($canAssign) {
                        // Update order: assign ke driver, ubah status ke 4 (ASSIGNED)
                        $order->update([
                            'driver_id' => $driver['user_id']
                        ]);

                        $driverOrders[] = [
                            'order_no' => $order->order_no,
                            'delivery_type' => $order->delivery->alias,
                            'delivery_lat' => $order->delivery_lat,
                            'delivery_lon' => $order->delivery_lon,
                        ];

                        $assignedCount++;
                        Log::info("✅ Order {$order->order_no} ({$order->delivery->alias}) → driver {$driver['user_id']}");
                    }
                }

                Log::info("👷 Driver {$driver['user_id']} total dapat " . count($driverOrders) . " pesanan.");
            }

            Log::info("🎯 Total order berhasil dialokasikan: {$assignedCount}");
        });
    }

}
