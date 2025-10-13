<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            // UUID sebagai primary key
            $table->id();

            $table->string('name');      // Nama shift, contoh: "Shift Pagi"
            $table->time('start_time');  // Jam mulai shift
            $table->time('end_time');    // Jam selesai shift

            $table->timestamps();        // created_at dan updated_at (tanpa timezone)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
