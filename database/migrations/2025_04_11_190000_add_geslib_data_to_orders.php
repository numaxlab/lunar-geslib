<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table($this->prefix.'orders', function (Blueprint $table) {
            $table->string('geslib_code', 50)->nullable();
            $table->dateTime('synced_with_geslib_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix.'geslib_orders', function (Blueprint $table) {
            $table->dropColumn('synced_with_geslib_at');
            $table->dropColumn('geslib_code');
        });
    }
};
