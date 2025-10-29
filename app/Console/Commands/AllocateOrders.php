<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OrderAllocationService;

class AllocateOrders extends Command
{
    protected $signature = 'orders:allocate';
    protected $description = 'Menjalankan algoritma alokasi pesanan ke driver STAY';

    public function handle(OrderAllocationService $service)
    {
        $service->allocateOrdersToDrivers();
        $this->info('✅ Alokasi pesanan selesai dijalankan.');
    }
}
