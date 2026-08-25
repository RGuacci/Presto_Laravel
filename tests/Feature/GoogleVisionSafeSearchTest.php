<?php

use App\Contracts\ImageSafeSearchAnalyzer;
use App\Jobs\GoogleVisionSafeSearch;
use App\Models\ArticleImage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Schema::create('article_images', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('article_id');
        $table->string('path');
        $table->string('watermarked_path')->nullable();
        $table->string('watermark_status')->default('pending');
        $table->timestamp('watermarked_at')->nullable();
        $table->string('card_path')->nullable();
        $table->string('processing_status')->default('pending');
        $table->json('vision_labels')->nullable();
        $table->string('vision_status')->default('pending');
        $table->timestamp('vision_analyzed_at')->nullable();
        $table->json('vision_safe_search')->nullable();
        $table->string('vision_safe_search_status')->default('pending');
        $table->timestamp('vision_safe_search_analyzed_at')->nullable();
        $table->unsignedInteger('position')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropAllTables();
});

it('stores the asynchronous SafeSearch analysis for an article image', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/42/image.jpg', 'image-contents');

    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/image.jpg',
        'position' => 1,
    ]);

    $safeSearchAnalyzer = new class implements ImageSafeSearchAnalyzer
    {
        public function analyze(string $imageContents): array
        {
            expect($imageContents)->toBe('image-contents');

            return [
                'adult' => 'very_unlikely',
                'spoof' => 'unlikely',
                'medical' => 'very_unlikely',
                'violence' => 'likely',
                'racy' => 'possible',
            ];
        }
    };

    (new GoogleVisionSafeSearch($articleImage->id))->handle($safeSearchAnalyzer);

    $articleImage->refresh();

    expect($articleImage->vision_safe_search_status)->toBe(ArticleImage::VISION_READY)
        ->and($articleImage->vision_safe_search)->toBe([
            'adult' => 'very_unlikely',
            'spoof' => 'unlikely',
            'medical' => 'very_unlikely',
            'violence' => 'likely',
            'racy' => 'possible',
        ])
        ->and($articleImage->vision_safe_search_analyzed_at)->not->toBeNull()
        ->and($articleImage->safeSearchRequiresReview())->toBeTrue();
});

it('marks the SafeSearch analysis as failed after all retries', function () {
    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/missing.jpg',
        'position' => 1,
    ]);

    (new GoogleVisionSafeSearch($articleImage->id))->failed(new RuntimeException('API unavailable'));

    expect($articleImage->refresh()->vision_safe_search_status)->toBe(ArticleImage::VISION_FAILED);
});
