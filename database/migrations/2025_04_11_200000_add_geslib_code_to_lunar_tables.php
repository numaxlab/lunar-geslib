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
        Schema::table($this->prefix.'collections', function (Blueprint $table): void {
            $table->string('geslib_code', 50)->nullable()->after('attribute_data');

            $table->index('geslib_code');
        });

        Schema::table($this->prefix.'brands', function (Blueprint $table): void {
            $table->string('geslib_code', 50)->nullable()->after('attribute_data');

            $table->index('geslib_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table($this->prefix.'brands', function (Blueprint $table): void {
            $table->dropIndex('geslib_code');
            $table->dropColumn('geslib_code');
        });

        Schema::table($this->prefix.'collections', function (Blueprint $table): void {
            $table->dropIndex('geslib_code');
            $table->dropColumn('geslib_code');
        });
    }
};
