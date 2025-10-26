<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status');
            $table->index('status', 'orders_status_index');
            $table->index('delivery_id', 'orders_delivery_id_index');
            $table->index('payment_type_id', 'orders_payment_type_id_index');
            $table->index('driver_id', 'orders_driver_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropIndex('orders_status_index');
            $table->dropIndex('orders_delivery_id_index');
            $table->dropIndex('orders_payment_type_id_index');  
            $table->dropIndex('orders_driver_id_index');
        });
    }
};
