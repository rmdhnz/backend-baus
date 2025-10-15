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
        $pending = Order::whereNull('driver_id')->get();
        $drivers = Driver::where('status', 'STAY')->get();

        if ($pending->isEmpty() || $drivers->isEmpty()) {
            return ['success'=>false, 'message'=>'No pending orders or no STAY drivers'];
        }

        $clusters = $this->clusterOrders($pending, $radius);
        $assigned = [];
        $skipped = [];

        DB::transaction(function () use ($clusters, $drivers, $maxPerDriver, &$assigned, &$skipped) {
            foreach ($clusters as $cluster) {
                $centroid = $this->centroid($cluster);
                $bestDriver = null;
                $bestDist = INF;

                foreach ($drivers as $driver) {
                    $currentLoad = DriverOrder::where('driver_id', $driver->id)
                        ->whereNull('completed_at')->count();
                    if ($currentLoad >= $maxPerDriver) continue;

                    $d = $this->gmap->getDistanceInMeters(
                        $driver->lat, $driver->lon,
                        $centroid['lat'], $centroid['lon']
                    );

                    if (!is_null($d) && $d < $bestDist) {
                        $bestDriver = $driver;
                        $bestDist = $d;
                    }
                }

                if ($bestDriver) {
                    foreach ($cluster as $order) {
                        DriverOrder::create([
                            'driver_id' => $bestDriver->id,
                            'order_id' => $order->id,
                            'assigned_at' => now(),
                        ]);
                        $order->update(['driver_id' => $bestDriver->id]);
                        $assigned[] = ['driver_id' => $bestDriver->id, 'order_id' => $order->id];
                    }
                } else {
                    foreach ($cluster as $order) {
                        $skipped[] = $order->id;
                    }
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

    private function clusterOrders($orders, int $radius): array
    {
        $clusters = [];
        $used = [];

        foreach ($orders as $o) {
            if (in_array($o->id, $used)) continue;
            $cluster = [$o];
            $used[] = $o->id;

            foreach ($orders as $other) {
                if (in_array($other->id, $used)) continue;
                $d = $this->gmap->getDistanceInMeters(
                    $o->delivery_lat, $o->delivery_lon,
                    $other->delivery_lat, $other->delivery_lon
                );
                if (!is_null($d) && $d <= $radius) {
                    $cluster[] = $other;
                    $used[] = $other->id;
                }
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }

    private function centroid(array $orders): array
    {
        $lat = array_sum(array_map(fn($o) => $o->delivery_lat, $orders)) / count($orders);
        $lon = array_sum(array_map(fn($o) => $o->delivery_lon, $orders)) / count($orders);
        return ['lat' => $lat, 'lon' => $lon];
    }
}
