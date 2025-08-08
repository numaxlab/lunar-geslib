<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create($this->prefix . 'geslib_inter_file_batch_lines', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('geslib_inter_file_id')
                ->constrained(
                    table: $this->prefix . 'geslib_inter_files',
                    indexName: 'geslib_inter_file_id_foreign',
                )
                ->cascadeOnDelete();
            $table->string('line_type', 5)->index();
            $table->string('article_id', 50)->index();
            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists($this->prefix . 'geslib_inter_file_batch_lines');
    }
};
