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
            $table->json('vision_labels')->nullable()->after('processing_status');
            $table->string('vision_status')->default('pending')->after('vision_labels');
            $table->timestamp('vision_analyzed_at')->nullable()->after('vision_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('article_images', function (Blueprint $table) {
            $table->dropColumn([
                'vision_labels',
                'vision_status',
                'vision_analyzed_at',
            ]);
        });
    }
};
