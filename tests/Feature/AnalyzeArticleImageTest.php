<?php

use App\Contracts\ImageLabelAnalyzer;
use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GoogleVisionSafeSearch;
use App\Models\ArticleImage;
use App\Services\GoogleVision\GoogleServiceAccountCredentials;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
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
    File::delete(storage_path('framework/testing/google-vision-credentials-test'));
});

it('analyzes an article image and stores the labels asynchronously', function () {
    Storage::fake('public');
    Storage::disk('public')->put('articles/42/bicycle.jpg', 'image-contents');

    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/bicycle.jpg',
        'position' => 1,
    ]);

    $imageLabelAnalyzer = new class implements ImageLabelAnalyzer
    {
        public function analyze(string $imageContents): array
        {
            expect($imageContents)->toBe('image-contents');

            return [
                ['description' => 'Bicycle', 'score' => 0.94],
                ['description' => 'Wheel', 'score' => 0.87],
            ];
        }
    };

    (new AnalyzeArticleImage($articleImage->id))->handle($imageLabelAnalyzer);

    $articleImage->refresh();

    expect($articleImage->vision_status)->toBe(ArticleImage::VISION_READY)
        ->and($articleImage->vision_labels)->toBe([
            ['description' => 'Bicycle', 'score' => 0.94],
            ['description' => 'Wheel', 'score' => 0.87],
        ])
        ->and($articleImage->vision_analyzed_at)->not->toBeNull();
});

it('marks an image analysis as failed after all retries', function () {
    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/missing.jpg',
        'position' => 1,
    ]);

    (new AnalyzeArticleImage($articleImage->id))->failed(new RuntimeException('API unavailable'));

    expect($articleImage->refresh()->vision_status)->toBe(ArticleImage::VISION_FAILED);
});

it('queues Google Vision analysis for existing pending images', function () {
    Queue::fake();

    $pendingImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/pending.jpg',
        'position' => 1,
    ]);

    ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/ready.jpg',
        'vision_labels' => [['description' => 'Table', 'score' => 0.91]],
        'vision_status' => ArticleImage::VISION_READY,
        'vision_analyzed_at' => now(),
        'position' => 2,
    ]);

    $this->artisan('articles:analyze-images')->assertSuccessful();

    Queue::assertPushed(
        AnalyzeArticleImage::class,
        fn (AnalyzeArticleImage $job): bool => $job->articleImageId === $pendingImage->id,
    );
    Queue::assertPushed(AnalyzeArticleImage::class, 1);
    Queue::assertPushed(
        GoogleVisionSafeSearch::class,
        fn (GoogleVisionSafeSearch $job): bool => $job->articleImageId === $pendingImage->id,
    );
    Queue::assertPushed(GoogleVisionSafeSearch::class, 2);
});

it('loads the existing embedded service account without copying it', function () {
    $credentialsPath = storage_path('framework/testing/google-vision-credentials-test');
    File::ensureDirectoryExists(dirname($credentialsPath));
    File::put($credentialsPath, <<<'CONTENTS'
/vendor
/node_modules
{
  "type": "service_account",
  "client_email": "vision-test@example.test",
  "private_key": "test-private-key"
}
CONTENTS);
    config(['services.google_vision.credentials_path' => $credentialsPath]);

    $credentials = (new GoogleServiceAccountCredentials)->load();

    expect($credentials)
        ->toMatchArray([
            'type' => 'service_account',
            'client_email' => 'vision-test@example.test',
            'private_key' => 'test-private-key',
        ]);
});
