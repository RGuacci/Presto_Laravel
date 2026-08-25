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
        Schema::create('revision_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('revisor_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('previous_status')->nullable();
            $table->boolean('new_status');
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->index(['revisor_id', 'undone_at', 'id']);
            $table->index(['article_id', 'undone_at', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_actions');
    }
};
