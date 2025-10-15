<?php

namespace App\Services\Geo;

class ClusteringService
{
    public function __construct(private GeoService $geo){}

    /**
     * DBSCAN sederhana untuk titik (lat, lon).
     * @param array<array{id:int, lat:float, lon:float}> $points
     * @param float $epsKm radius km (0.3 = 300 m)
     * @param int $minPts minimal titik untuk jadi cluster (default 1 agar single-order tetap cluster)
     * @return array<array{ids:int[], centroid:array{lat:float, lon:float}}>
     */
    public function dbscan(array $points, float $epsKm = 0.3, int $minPts = 1): array
    {
        $visited = [];
        $clusters = [];
        $noise = [];
        $clusterId = 0;

        $neighborsCache = [];

        $getNeighbors = function($idx) use (&$points, $epsKm, &$neighborsCache) {
            if (isset($neighborsCache[$idx])) return $neighborsCache[$idx];
            $neighbors = [];
            [$ilat, $ilon] = [$points[$idx]['lat'], $points[$idx]['lon']];
            foreach ($points as $j => $p) {
                $d = $this->geo->distanceKm($ilat, $ilon, $p['lat'], $p['lon']);
                if ($d <= $epsKm) $neighbors[] = $j;
            }
            return $neighborsCache[$idx] = $neighbors;
        };

        for ($i=0; $i<count($points); $i++) {
            if (!empty($visited[$i])) continue;
            $visited[$i] = true;

            $neighbors = $getNeighbors($i);
            if (count($neighbors) < $minPts) {
                $noise[] = $i;
                continue;
            }

            $clusters[$clusterId] = ['members'=>[]];
            $seeds = $neighbors;
            foreach ($seeds as $s) {
                if (empty($visited[$s])) {
                    $visited[$s] = true;
                    $n2 = $getNeighbors($s);
                    if (count($n2) >= $minPts) {
                        $seeds = array_values(array_unique(array_merge($seeds, $n2)));
                    }
                }
                $alreadyIn = false;
                foreach ($clusters[$clusterId]['members'] as $m) {
                    if ($m === $s) { $alreadyIn = true; break; }
                }
                if (!$alreadyIn) $clusters[$clusterId]['members'][] = $s;
            }
            $clusterId++;
        }

        $result = [];
        foreach ($clusters as $c) {
            $ids = [];
            $sumLat = 0; $sumLon = 0;
            foreach ($c['members'] as $idx) {
                $ids[] = $points[$idx]['id'];
                $sumLat += $points[$idx]['lat'];
                $sumLon += $points[$idx]['lon'];
            }
            $n = max(1, count($c['members']));
            $result[] = [
                'ids' => $ids,
                'centroid' => ['lat' => $sumLat/$n, 'lon' => $sumLon/$n],
            ];
        }
        foreach ($noise as $idx) {
            $p = $points[$idx];
            $result[] = [
                'ids' => [$p['id']],
                'centroid' => ['lat' => $p['lat'], 'lon' => $p['lon']],
            ];
        }

        return $result;
    }
}
