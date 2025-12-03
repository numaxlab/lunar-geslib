<?php

declare(strict_types=1);

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
        Schema::create($this->prefix.'geslib_trusted_stock_providers', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('sinli_id');
            $table->string('delivery_period');
            $table->string('sort_position')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'geslib_trusted_stock_providers');
    }
};
