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
            $table->json('vision_safe_search')->nullable()->after('vision_analyzed_at');
            $table->string('vision_safe_search_status')->default('pending')->after('vision_safe_search');
            $table->timestamp('vision_safe_search_analyzed_at')->nullable()->after('vision_safe_search_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_images', function (Blueprint $table) {
            $table->dropColumn([
                'vision_safe_search',
                'vision_safe_search_status',
                'vision_safe_search_analyzed_at',
            ]);
        });
    }
};
