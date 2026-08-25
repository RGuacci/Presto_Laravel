<?php

use App\Contracts\ImageWatermarker;
use App\Jobs\AddArticleImageWatermark;
use App\Jobs\GenerateArticleCardThumbnail;
use App\Models\ArticleImage;
use Illuminate\Database\Schema\Blueprint;
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
});

it('creates a visible logo watermark without modifying the original image', function () {
    Storage::fake('public');
    Queue::fake();

    $sourceImage = imagecreatetruecolor(600, 400);
    $white = imagecolorallocate($sourceImage, 255, 255, 255);
    imagefill($sourceImage, 0, 0, $white);

    ob_start();
    imagepng($sourceImage);
    $sourceContents = ob_get_clean();
    imagedestroy($sourceImage);

    expect($sourceContents)->toBeString()->not->toBeEmpty();

    $sourcePath = 'articles/42/source.png';
    Storage::disk('public')->put($sourcePath, $sourceContents);

    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => $sourcePath,
        'position' => 1,
    ]);

    (new AddArticleImageWatermark($articleImage->id))
        ->handle(app(ImageWatermarker::class));

    $articleImage->refresh();

    expect($articleImage->watermark_status)->toBe(ArticleImage::WATERMARK_READY)
        ->and($articleImage->watermarked_path)->toBe("articles/42/watermarked/{$articleImage->id}.webp")
        ->and($articleImage->watermarked_at)->not->toBeNull()
        ->and(Storage::disk('public')->get($sourcePath))->toBe($sourceContents)
        ->and(Storage::disk('public')->exists($articleImage->watermarked_path))->toBeTrue();

    Queue::assertPushed(
        GenerateArticleCardThumbnail::class,
        fn (GenerateArticleCardThumbnail $job): bool => $job->articleImageId === $articleImage->id,
    );

    $watermarkedContents = Storage::disk('public')->get($articleImage->watermarked_path);
    $watermarkedSize = getimagesizefromstring($watermarkedContents);
    $watermarkedImage = imagecreatefromstring($watermarkedContents);

    expect($watermarkedSize)->toBeArray()
        ->and($watermarkedSize[0])->toBe(600)
        ->and($watermarkedSize[1])->toBe(400)
        ->and($watermarkedSize[2])->toBe(IMAGETYPE_WEBP)
        ->and($watermarkedImage)->toBeInstanceOf(GdImage::class);

    $visibleLogoPixelFound = false;

    for ($x = 480; $x < 595 && ! $visibleLogoPixelFound; $x += 4) {
        for ($y = 270; $y < 395; $y += 4) {
            $pixel = imagecolorsforindex($watermarkedImage, imagecolorat($watermarkedImage, $x, $y));

            if (min($pixel['red'], $pixel['green'], $pixel['blue']) < 200) {
                $visibleLogoPixelFound = true;

                break;
            }
        }
    }

    imagedestroy($watermarkedImage);

    expect($visibleLogoPixelFound)->toBeTrue();
});

it('marks watermark processing as failed after all retries', function () {
    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/missing.png',
        'position' => 1,
    ]);

    (new AddArticleImageWatermark($articleImage->id))->failed(new RuntimeException('GD unavailable'));

    expect($articleImage->refresh()->watermark_status)->toBe(ArticleImage::WATERMARK_FAILED);
});

it('queues watermark generation only for images that need it', function () {
    Queue::fake();

    $pendingImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/pending.png',
        'position' => 1,
    ]);

    ArticleImage::query()->create([
        'article_id' => 43,
        'path' => 'articles/43/ready.png',
        'watermarked_path' => 'articles/43/watermarked/ready.webp',
        'watermark_status' => ArticleImage::WATERMARK_READY,
        'watermarked_at' => now(),
        'position' => 1,
    ]);

    $this->artisan('articles:watermark-images')->assertSuccessful();

    Queue::assertPushed(
        AddArticleImageWatermark::class,
        fn (AddArticleImageWatermark $job): bool => $job->articleImageId === $pendingImage->id,
    );
    Queue::assertPushed(AddArticleImageWatermark::class, 1);
});
