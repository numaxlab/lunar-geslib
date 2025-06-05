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
        Schema::create('lunar_geslib_order_sync_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('geslib_endpoint_called');
            $table->string('status'); // e.g., 'success', 'error'
            $table->text('message')->nullable();
            $table->text('payload_to_geslib')->nullable();
            $table->text('payload_from_geslib')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lunar_geslib_order_sync_log');
    }
};
