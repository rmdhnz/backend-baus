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
        Schema::create('pending_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string("olsera_order_id");
            $table->string("olsera_order_no");
            $table->string("reason");
            $table->timestamps();

            $table->index('order_id','pending_orders,order_id_index');
            $table->index('olsera_order_id','pending_olera_order_id');
            $table->index('olsera_order_no','pending_olera_order_no');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_orders');
    }
};
