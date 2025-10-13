<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
