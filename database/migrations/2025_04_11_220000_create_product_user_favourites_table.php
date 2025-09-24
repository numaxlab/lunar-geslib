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
        Schema::create($this->prefix.'geslib_product_user_favourites', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');

            $table
                ->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table
                ->foreign('product_id')
                ->references('id')->on($this->prefix.'products')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'geslib_product_user_favourites');
    }
};
