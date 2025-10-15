<?php 
namespace App\Services;

use App\Models\Driver;
use App\Services\GMapService;

class OrderMappingService
{
    protected $gmap;

    public function __construct(GMapService $gmap)
    {
        $this->gmap = $gmap;
    }

    public function mapOrders(array $orders): array
    {
        $drivers = Driver::with('user')
            ->where('status', 'STAY')
            ->get()
            ->map(fn($d) => [
                'driver_id' => $d->user_id,
                'orders' => [],
                'types' => ['EX' => 0, 'IN' => 0],
                'locations' => [], 
            ])
            ->toArray();

        $waiting = [];

        foreach ($orders as $order) {
            $assigned = false;

            foreach ($drivers as &$driver) {
                if (count($driver['orders']) >= 4) continue;

                $type = $order['delivery_type'];
                $typeLimitReached = in_array($type, ['EX', 'IN']) && $driver['types'][$type] >= 2;
                if ($typeLimitReached) continue;

                $withinDistance = true;

                foreach ($driver['locations'] as $loc) {
                    $distance = $this->gmap->getDistanceInMeters(
                        $order['customer']['lat'],
                        $order['customer']['lon'],
                        $loc['lat'],
                        $loc['lon']
                    );

                    echo "Jarak : ". $distance . " m\n";

                    if (!$distance || $distance > 300) {
                        $withinDistance = false;
                        break;
                    }
                }

                if (!$withinDistance) continue;

                $driver['orders'][] = $order['order_no'];
                $driver['types'][$type] = ($driver['types'][$type] ?? 0) + 1;
                $driver['locations'][] = [
                    'lat' => $order['customer']['lat'],
                    'lon' => $order['customer']['lon'],
                ];
                $assigned = true;
                break;
            }

            if (!$assigned) {
                $waiting[] = $order['order_no'];
            }
        }

        return [
            'mapping' => collect($drivers)
                ->filter(fn($d) => count($d['orders']) > 0)
                ->map(fn($d) => [
                    'driver_id' => $d['driver_id'],
                    'orders' => $d['orders'],
                ])
                ->values()
                ->all(),
            'waiting' => $waiting,
            'ok' => true,
        ];
    }
}
