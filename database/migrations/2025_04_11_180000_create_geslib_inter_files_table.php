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
            $table->string('status');
            $table->dateTime('received_at');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->integer('total_lines')->default(0);
            $table->integer('processed_lines')->default(0);
            $table->json('log')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'geslib_inter_files');
    }
};
