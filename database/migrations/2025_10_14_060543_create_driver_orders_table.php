<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_orders', function (Blueprint $table) {
            $table->id();

            // driver -> merujuk ke users.id (nullable, set null on delete)
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // order -> merujuk ke orders.id (cascade on delete)
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Waktu assign & selesai
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            // Index tambahan untuk query cepat (misal: driver active orders)
            $table->index(['driver_id','order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_orders');
    }
};
