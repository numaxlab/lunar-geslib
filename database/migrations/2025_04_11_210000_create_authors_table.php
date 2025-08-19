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
        Schema::create($this->prefix.'geslib_authors', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->json('attribute_data')->nullable();
            $table->string('geslib_code', 50)->nullable();

            $table->timestamps();
        });

        Schema::create($this->prefix.'geslib_author_product', function (Blueprint $table): void {
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('product_id');
            $table->string('author_type')->default(\NumaxLab\Geslib\Lines\AuthorType::AUTHOR);
            $table->integer('position')->default(0);

            $table
                ->foreign('author_id')
                ->references('id')->on($this->prefix.'geslib_authors')
                ->onDelete('cascade');
            $table
                ->foreign('product_id')
                ->references('id')->on($this->prefix.'products')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'geslib_author_product');
        Schema::dropIfExists($this->prefix.'geslib_authors');
    }
};
