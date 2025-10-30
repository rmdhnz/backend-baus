<?php

namespace App\Services;

use App\Models\Staff_IM;
use Carbon\Carbon;

class StaffIMService
{
    /**
     * Ambil semua staff yang sedang dalam shift aktif
     * @return array
     */
    public function getStaffInShift(): array
    {
        $staffs = Staff_IM::with(['shift', 'user'])->get();
        $now = now()->format('H:i:s');
        $current = Carbon::createFromFormat('H:i:s', $now);

        $staffInShift = [];

        foreach ($staffs as $staff) {
            if (!$staff->shift) continue;

            $start = Carbon::createFromFormat('H:i:s', $staff->shift->start_time);
            $end = Carbon::createFromFormat('H:i:s', $staff->shift->end_time);

            // Handle shift yang melewati tengah malam
            if ($end->lessThan($start)) {
                $inShift = $current->greaterThanOrEqualTo($start) || $current->lessThanOrEqualTo($end);
            } else {
                $inShift = $current->between($start, $end);
            }

            if ($inShift) {
                $staffInShift[] = [
                    'user_id' => $staff->user_id,
                    'name' => $staff->user->name ?? null,
                    'shift_name' => $staff->shift->name,
                    'start_time' => $staff->shift->start_time,
                    'end_time' => $staff->shift->end_time,
                ];
            }
        }
        return $staffInShift;
    }
}
