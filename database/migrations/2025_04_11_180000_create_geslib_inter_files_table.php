<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create($this->prefix . 'geslib_inter_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('received_at');
            $table->boolean('processing')->default(false);
            $table->boolean('processed')->default(false);
            $table->json('log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'geslib_inter_files');
    }
};
