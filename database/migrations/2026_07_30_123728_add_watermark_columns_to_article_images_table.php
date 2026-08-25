<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('article_images', function (Blueprint $table) {
            $table->string('watermarked_path')->nullable()->after('path');
            $table->string('watermark_status')->default('pending')->after('watermarked_path');
            $table->timestamp('watermarked_at')->nullable()->after('watermark_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_images', function (Blueprint $table) {
            $table->dropColumn([
                'watermarked_path',
                'watermark_status',
                'watermarked_at',
            ]);
        });
    }
};
