<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Enable pgcrypto for gen_random_uuid()
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto;');
    }

    public function down(): void
    {
        // Optional: biasanya extension dibiarkan
        DB::statement('DROP EXTENSION IF EXISTS pgcrypto;');
    }
};
