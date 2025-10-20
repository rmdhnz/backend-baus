<?php

namespace App\Services\Allocation;

use App\Models\Order;
use App\Models\Driver;
use App\Models\DriverOrder;
use App\Services\GMapService;
use Illuminate\Support\Facades\DB;

class HybridAllocationService
{
    public function __construct(private GMapService $gmap) {}

    public function allocate(int $maxPerDriver = 4, int $radius = 300): array
    {
        // Prioritaskan EX dan I
        $pending = Order::with('delivery')
            ->whereNull('driver_id')
            ->get()
            ->sortBy(function ($order) {
                return $order->delivery->alias === 'FD' ? 1 : 0;
            });

        $drivers = Driver::where('status', 'STAY')->get();

        if ($pending->isEmpty() || $drivers->isEmpty()) {
            return ['success' => false, 'message' => 'No pending orders or no STAY drivers'];
        }

        $clusters = $this->clusterOrders($pending, $radius, $maxPerDriver);
        $assigned = [];
        $skipped = [];

        DB::transaction(function () use ($clusters, $drivers, &$assigned, &$skipped) {
            $driverPool = $drivers->values();
            $driverIndex = 0;

            foreach ($clusters as $cluster) {
                if ($driverIndex >= $driverPool->count()) {
                    foreach ($cluster as $o) {
                        $skipped[] = $o->id;
                    }
                    continue;
                }

                $driver = $driverPool[$driverIndex];
                $driverIndex++;

                foreach ($cluster as $order) {
                    DriverOrder::create([
                        'driver_id' => $driver->id,
                        'order_id' => $order->id,
                        'assigned_at' => now(),
                    ]);
                    $order->update(['driver_id' => $driver->id]);
                    $assigned[] = ['driver_id' => $driver->id, 'order_id' => $order->id];
                }
            }
        });

        return [
            'success' => true,
            'strategy' => 'hybrid',
            'assigned' => $assigned,
            'skipped' => $skipped,
        ];
    }

    private function clusterOrders($orders, int $radius, int $maxPerCluster): array
    {
        $clusters = [];
        $used = [];

        foreach ($orders as $o) {
            if (in_array($o->id, $used)) continue;

            $cluster = [$o];
            $used[] = $o->id;
            $hasExpress = in_array($o->delivery->alias, ['EX', 'I']);

            foreach ($orders as $other) {
                if (in_array($other->id, $used)) continue;
                if (count($cluster) >= $maxPerCluster) break;

                if ($hasExpress && in_array($other->delivery->alias, ['EX', 'I'])) continue;

                $ok = true;
                foreach ($cluster as $existing) {
                    $d = $this->gmap->getDistanceInMeters(
                        $existing->delivery_lat, $existing->delivery_lon,
                        $other->delivery_lat, $other->delivery_lon
                    );
                    if (is_null($d) || $d > $radius) {
                        $ok = false;
                        break;
                    }
                }

                if ($ok) {
                    $cluster[] = $other;
                    $used[] = $other->id;
                }
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }
}
