<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->prefix . 'geslib_order_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->string('geslib_endpoint_called');
            $table->string('status');
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
        Schema::dropIfExists($this->prefix . 'geslib_order_sync_logs');
    }
};
