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
    $pending = Order::whereNull('driver_id')
        ->with('delivery') // supaya bisa akses alias tanpa join manual
        ->get();

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
                $driverId = $driver->user_id;

                // Hitung sisa slot driver
                $remaining = $maxPerDriver - DriverOrder::where('driver_id', $driverId)
                    ->whereNull('completed_at')->count();

                if ($remaining <= 0) continue;

                // Ambil pesanan aktif yang sudah dimiliki driver
                $existingCoords = DriverOrder::where('driver_id', $driverId)
                    ->whereNull('completed_at')
                    ->with('order:id,delivery_lat,delivery_lon')
                    ->get()
                    ->map(fn($o) => [$o->order->delivery_lat, $o->order->delivery_lon])
                    ->toArray();

                $hasExpressOrInstant = DriverOrder::where('driver_id', $driverId)
                    ->whereNull('completed_at')
                    ->whereHas('order.delivery', function ($q) {
                        $q->whereIn('alias', ['EX', 'I']);
                    })->exists();

                while ($remaining > 0 && $unassigned->isNotEmpty()) {
                    $bestOrderId = null;
                    $bestDist = INF;

                    foreach ($unassigned as $order) {
                        // aturan express/instant hanya satu per driver
                        if (in_array($order->delivery->alias, ['EX', 'I']) && $hasExpressOrInstant) {
                            continue;
                        }

                        // aturan jarak antar pesanan
                        if (!$this->intraWithin($existingCoords, $order->delivery_lat, $order->delivery_lon)) {
                            continue;
                        }

                        // tanpa pertimbangan lokasi driver (driver tidak punya lat/lon)
                        $bestOrderId = $order->id;
                        break;
                    }

                    if (is_null($bestOrderId)) break;

                    $order = $unassigned->pull($bestOrderId);

                    DriverOrder::create([
                        'driver_id' => $driverId,
                        'order_id' => $order->id,
                        'assigned_at' => now(),
                    ]);

                    $order->update(['driver_id' => $driverId]);

                    $assigned[] = [
                        'driver_id' => $driverId,
                        'order_id' => $order->id,
                    ];

                    $existingCoords[] = [$order->delivery_lat, $order->delivery_lon];

                    if (in_array($order->delivery->alias, ['EX', 'I'])) {
                        $hasExpressOrInstant = true;
                    }

                    $remaining--;
                }
            }
        }

        // sisanya ditandai skipped
        foreach ($unassigned as $order) {
            $skipped[] = $order->id;
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

