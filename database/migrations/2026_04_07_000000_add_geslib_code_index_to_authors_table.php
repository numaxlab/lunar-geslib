<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table($this->prefix . 'geslib_authors', function (Blueprint $table): void {
            $table->index('geslib_code');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix . 'geslib_authors', function (Blueprint $table): void {
            $table->dropIndex(['geslib_code']);
        });
    }
};
