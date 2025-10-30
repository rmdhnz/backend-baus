<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
class DriverService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env("URL_LIST_DRIVER");
    }

    public function getAllDrivers()
    {
        try {
            $response = Http::get($this->baseUrl);

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'data' => $response->json(),
                ];
            }

            Log::error('Failed to fetch drivers', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data driver dari API eksternal.',
            ];
        } catch (\Exception $e) {
            Log::error('DriverService exception', ['error' => $e->getMessage()]);

            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghubungi service driver.',
                'detail' => $e->getMessage(),
            ];
        }
    }

    public function getDriverInShift() {
        $drivers = Driver::with(['shift','user'])->get();
        $now = now()->format('H:i:s');
        $current = Carbon::createFromFormat('H:i:s', $now);

        $driverInShift = [];

        foreach($drivers as $driver) { 
            if(!$driver->shift) continue;
            $start = Carbon::createFromFormat("H:i:s",$driver->shift->start_time);
            $end = Carbon::createFromFormat('H:i:s', $driver->shift->end_time);
            if ($end->lessThan($start)) {
                $inShift = $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
            } else {
                $inShift = $current->between($start, $end);
            }

            if($inShift) { 
                $driverInShift[] = [
                    'user_id' => $driver->user_id,
                    'name' => $driver->user->name ?? null,
                    'shift_name' => $driver->shift->name,
                    'start_time' => $driver->shift->start_time,
                    'end_time' => $driver->shift->end_time,
                ];
            }
        }
        return $driverInShift;
    }
}
