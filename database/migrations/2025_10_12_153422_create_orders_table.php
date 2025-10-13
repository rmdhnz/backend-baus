<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            // Primary key auto increment
            $table->id();

            // Olsera order ID (string), di-index
            $table->string('order_id')->index();

            // Nama customer
            $table->string('cust_name')->nullable();

            // Relasi ke tabel users
            $table->unsignedBigInteger('driver_id')->nullable();
            $table->unsignedBigInteger('staff_im_id')->nullable();

            // Nomor order internal, unik
            $table->string('order_no')->unique();

            // Link ke struk
            $table->string('receipt_link')->nullable();

            // List item dalam format JSON
            $table->json('items')->nullable();

            // Nilai-nilai finansial
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
             // Tambahan dari gambar kedua
            $table->decimal('distance_km', 8, 2)->default(0);   // contoh: 12.35 km
            $table->decimal('delivery_lon', 10, 6)->nullable(); // contoh: 106.827153
            $table->decimal('delivery_lat', 10, 6)->nullable(); // contoh:  -6.175392
            $table->string('delivery_link')->nullable();        // url gmaps / tracker
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();  

            // Timestamp Laravel standar
            $table->timestamps();

            // Foreign key relasi ke tabel users
            $table->foreign('driver_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('staff_im_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
