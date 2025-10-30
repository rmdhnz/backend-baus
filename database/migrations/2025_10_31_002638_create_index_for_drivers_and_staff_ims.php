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
        Schema::table('drivers', function (Blueprint $table) {
            // Tambahkan index jika belum ada
            if (!Schema::hasColumn('drivers', 'user_id')) {
                throw new Exception("Kolom 'user_id' tidak ditemukan di tabel drivers");
            }
            $table->index('user_id', 'drivers_user_id_index');
            $table->index('shift_id','drivers_shift_id_index');
        });

        Schema::table('staff_ims', function (Blueprint $table) {
            if (!Schema::hasColumn('staff_ims', 'user_id')) {
                throw new Exception("Kolom 'user_id' tidak ditemukan di tabel staff_ims");
            }
            $table->index('user_id', 'staff_ims_user_id_index');
            $table->index('shift_id','staff_ims_shift_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropIndex(['drivers_user_id_index','drivers_shift_id_index']);
        });

        Schema::table('staff_ims', function (Blueprint $table) {
            $table->dropIndex(['staff_ims_user_id_index','staff_ims_shift_id_index']);
        });
    }
};
