<?php 
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GMapService
{
    protected string $key;

    public function __construct()
    {
        $this->key = config('services.google_maps.key');
    }

    public function getDistanceInMeters($fromLat, $fromLng, $toLat, $toLng): ?float
    {
        $response = Http::get("https://maps.googleapis.com/maps/api/distancematrix/json", [
            'origins' => "$fromLat,$fromLng",
            'destinations' => "$toLat,$toLng",
            'key' => $this->key,
        ]);

        $data = $response->json();

        if (isset($data['rows'][0]['elements'][0]['distance']['value'])) {
            return $data['rows'][0]['elements'][0]['distance']['value'];
        }

        return null;
    }
    public function getTravelDistance(array $origin, array $destination, string $mode = 'driving'): ?array
    {
        $baseUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';

        $params = [
            'origins' => "{$origin['lat']},{$origin['lon']}",
            'destinations' => "{$destination['lat']},{$destination['lon']}",
            'mode' => $mode,
            'key' => config('services.gmaps.key'),
        ];

        $response = Http::get($baseUrl, $params);

        if (!$response->successful()) {
            return $this->defaultDistance();
        }

        $data = $response->json();
        if (($data['status'] ?? null) !== 'OK') {
            return $this->defaultDistance();
        }

        $element = $data['rows'][0]['elements'][0] ?? null;
        if (!$element || ($element['status'] ?? null) !== 'OK') {
            return $this->defaultDistance();
        }

        return [
            'distance_text'   => $element['distance']['text'] ?? '0 km',
            'distance_meters' => $element['distance']['value'] ?? 0,
            'duration_text'   => $element['duration']['text'] ?? '0 mins',
            'duration_seconds'=> $element['duration']['value'] ?? 0,
        ];
    }
    private function defaultDistance(): array
    {
        return [
            'distance_text'   => '0 km',
            'distance_meters' => 0,
            'duration_text'   => '0 mins',
            'duration_seconds'=> 0,
        ];
    }
    function latLonToGmapUrl($lat, $lon)
    {
        return "https://www.google.com/maps?q={$lat},{$lon}";
    }
}
