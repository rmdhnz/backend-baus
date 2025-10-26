<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi: tambahkan kolom device_id + index
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom device_id, nullable supaya user lama tetap valid
            $table->string('device_id', 100)->nullable()->after('phone');

            // Tambahkan index untuk mempercepat pencarian login berdasarkan device_id
            $table->index('device_id', 'users_device_id_index');
        });
    }

    /**
     * Rollback migrasi: hapus index & kolom device_id
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus index dulu sebelum drop kolom
            $table->dropIndex('users_device_id_index');
            $table->dropColumn('device_id');
        });
    }
};
