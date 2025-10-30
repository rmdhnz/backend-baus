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
            $table->foreignId('driver_condition_id')->nullable()->constrained()->cascadeOnDelete();
            $table->dropColumn("status");
            $table->index("driver_condition_id","drivers_driver_cond_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('driver_condition_id');
            $table->string("status");
            $table->dropIndex('drivers_driver_cond_id');
        });
    }
};
