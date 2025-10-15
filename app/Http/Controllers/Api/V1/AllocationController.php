<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Allocation\BatchedAllocationService;
use App\Services\Allocation\HybridAllocationService;

class AllocationController extends Controller
{
    public function __construct(
        private BatchedAllocationService $batched,
        private HybridAllocationService $hybrid
    ) {}

    public function run(Request $request)
    {
        $validated = $request->validate([
            'strategy' => 'required|in:batched,hybrid',
            'options.batch_size' => 'nullable|integer|min:1',
            'options.max_orders_per_driver' => 'nullable|integer|min:1',
            'options.radius' => 'nullable|integer|min:100',
        ]);

        $maxPerDriver = $validated['options']['max_orders_per_driver'] ?? 4;

        if ($validated['strategy'] === 'batched') {
            $batchSize = $validated['options']['batch_size'] ?? 10;
            $result = $this->batched->allocate($batchSize, $maxPerDriver);
        } else {
            $radius = $validated['options']['radius'] ?? 300;
            $result = $this->hybrid->allocate($maxPerDriver, $radius);
        }

        return response()->json($result);
    }
}
