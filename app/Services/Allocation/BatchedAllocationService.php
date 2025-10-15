<?php

namespace App\Services\Allocation;

use App\Models\Order;
use App\Models\Driver;
use App\Models\DriverOrder;
use App\Services\GMapService;
use Illuminate\Support\Facades\DB;

class BatchedAllocationService
{
    public function __construct(private GMapService $gmap) {}

    public function allocate(int $batchSize = 10, int $maxPerDriver = 4): array
    {
        $pending = Order::whereNull('driver_id')->get();
        $drivers = Driver::where('status', 'STAY')->get();

        if ($pending->isEmpty() || $drivers->isEmpty()) {
            return ['success'=>false, 'message'=>'No pending orders or no STAY drivers'];
        }

        $driverBatches = $drivers->chunk($batchSize);
        $assigned = [];
        $skipped  = [];

        DB::transaction(function () use ($driverBatches, $pending, $maxPerDriver, &$assigned, &$skipped) {
            $unassigned = $pending->keyBy('id');

            foreach ($driverBatches as $batch) {
                foreach ($batch as $driver) {
                    $remaining = $maxPerDriver - DriverOrder::where('driver_id', $driver->id)
                        ->whereNull('completed_at')->count();
                    if ($remaining <= 0) continue;

                    $existingCoords = DriverOrder::where('driver_id', $driver->id)
                        ->whereNull('completed_at')
                        ->with('order:id,delivery_lat,delivery_lon')
                        ->get()
                        ->map(fn($o) => [$o->order->delivery_lat, $o->order->delivery_lon])
                        ->toArray();

                    while ($remaining > 0 && $unassigned->isNotEmpty()) {
                        $bestOrderId = null;
                        $bestDist = INF;

                        foreach ($unassigned as $order) {
                            if (!$this->intraWithin($existingCoords, $order->delivery_lat, $order->delivery_lon)) continue;

                            $d = $this->gmap->getDistanceInMeters(
                                $driver->lat, $driver->lon,
                                $order->delivery_lat, $order->delivery_lon
                            );

                            if (is_null($d) || $d > $bestDist) continue;

                            $bestDist = $d;
                            $bestOrderId = $order->id;
                        }

                        if (is_null($bestOrderId)) break;

                        $order = $unassigned->pull($bestOrderId);
                        DriverOrder::create([
                            'driver_id' => $driver->id,
                            'order_id' => $order->id,
                            'assigned_at' => now(),
                        ]);

                        $order->update(['driver_id' => $driver->id]);
                        $assigned[] = ['driver_id' => $driver->id, 'order_id' => $order->id];
                        $existingCoords[] = [$order->delivery_lat, $order->delivery_lon];
                        $remaining--;
                    }
                }
            }
        });

        return [
            'success' => true,
            'strategy' => 'batched',
            'assigned' => $assigned,
            'skipped' => $skipped,
        ];
    }

    private function intraWithin(array $existing, float $lat, float $lon, int $threshold = 300): bool
    {
        foreach ($existing as [$elat, $elon]) {
            $d = $this->gmap->getDistanceInMeters($elat, $elon, $lat, $lon);
            if (is_null($d) || $d > $threshold) return false;
        }
        return true;
    }
}
