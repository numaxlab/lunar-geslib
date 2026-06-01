<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lunar\Base\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table($this->prefix.'geslib_inter_files', function (Blueprint $table): void {
            $table->string('file_type')->default('normal_update')->index()->after('status');
            $table->json('strategy_metadata')->nullable()->after('file_type');
        });
    }

    public function down(): void
    {
        Schema::table($this->prefix.'geslib_inter_files', function (Blueprint $table): void {
            $table->dropIndex([$this->prefix === '' ? 'geslib_inter_files_file_type_index' : $this->prefix.'geslib_inter_files_file_type_index']);
            $table->dropColumn(['file_type', 'strategy_metadata']);
        });
    }
};
