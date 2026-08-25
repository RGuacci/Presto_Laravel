<?php

use App\Contracts\ImageWatermarker;
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

it('generates a cropped 300 by 300 WebP thumbnail for a card', function () {
    Storage::fake('public');

    $sourceImage = imagecreatetruecolor(600, 300);
    $leftColour = imagecolorallocate($sourceImage, 230, 95, 45);
    $rightColour = imagecolorallocate($sourceImage, 41, 41, 41);
    imagefilledrectangle($sourceImage, 0, 0, 299, 299, $leftColour);
    imagefilledrectangle($sourceImage, 300, 0, 599, 299, $rightColour);

    ob_start();
    imagepng($sourceImage);
    $sourceContents = ob_get_clean();
    imagedestroy($sourceImage);

    expect($sourceContents)->toBeString()->not->toBeEmpty();

    $sourcePath = 'articles/42/source.png';
    $watermarkedPath = 'articles/42/watermarked/1.webp';
    Storage::disk('public')->put($sourcePath, $sourceContents);
    Storage::disk('public')->put($watermarkedPath, $sourceContents);

    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => $sourcePath,
        'watermarked_path' => $watermarkedPath,
        'watermark_status' => ArticleImage::WATERMARK_READY,
        'watermarked_at' => now(),
        'position' => 1,
    ]);

    (new GenerateArticleCardThumbnail($articleImage->id))
        ->handle(app(ImageWatermarker::class));

    $articleImage->refresh();

    expect($articleImage->processing_status)->toBe(ArticleImage::PROCESSING_READY)
        ->and($articleImage->card_path)->toBe("articles/42/cards/{$articleImage->id}.webp")
        ->and(Storage::disk('public')->exists($articleImage->card_path))->toBeTrue();

    $thumbnailSize = getimagesizefromstring(
        Storage::disk('public')->get($articleImage->card_path),
    );

    expect($thumbnailSize)->toBeArray()
        ->and($thumbnailSize[0])->toBe(GenerateArticleCardThumbnail::WIDTH)
        ->and($thumbnailSize[1])->toBe(GenerateArticleCardThumbnail::HEIGHT)
        ->and($thumbnailSize[2])->toBe(IMAGETYPE_WEBP);
});

it('queues missing thumbnails without reprocessing completed images', function () {
    Queue::fake();

    $pendingImage = ArticleImage::query()->create([
        'article_id' => 10,
        'path' => 'articles/10/source.png',
        'position' => 1,
    ]);

    ArticleImage::query()->create([
        'article_id' => 11,
        'path' => 'articles/11/source.png',
        'card_path' => 'articles/11/cards/ready.webp',
        'processing_status' => ArticleImage::PROCESSING_READY,
        'position' => 1,
    ]);

    $this->artisan('articles:generate-card-thumbnails')
        ->assertSuccessful();

    Queue::assertPushed(
        GenerateArticleCardThumbnail::class,
        fn (GenerateArticleCardThumbnail $job): bool => $job->articleImageId === $pendingImage->id,
    );
    Queue::assertPushed(GenerateArticleCardThumbnail::class, 1);
});

it('places the logo after the square crop so it remains visible', function () {
    Storage::fake('public');

    $sourceImage = imagecreatetruecolor(900, 300);
    $white = imagecolorallocate($sourceImage, 255, 255, 255);
    imagefill($sourceImage, 0, 0, $white);

    ob_start();
    imagepng($sourceImage);
    $sourceContents = ob_get_clean();
    imagedestroy($sourceImage);

    expect($sourceContents)->toBeString()->not->toBeEmpty();

    Storage::disk('public')->put('articles/42/source.png', $sourceContents);
    $articleImage = ArticleImage::query()->create([
        'article_id' => 42,
        'path' => 'articles/42/source.png',
        'position' => 1,
    ]);

    (new GenerateArticleCardThumbnail($articleImage->id))
        ->handle(app(ImageWatermarker::class));

    $thumbnail = imagecreatefromstring(
        Storage::disk('public')->get($articleImage->refresh()->card_path),
    );
    $visibleLogoPixelFound = false;

    for ($x = 215; $x < 295 && ! $visibleLogoPixelFound; $x += 3) {
        for ($y = 210; $y < 295; $y += 3) {
            $pixel = imagecolorsforindex($thumbnail, imagecolorat($thumbnail, $x, $y));

            if (min($pixel['red'], $pixel['green'], $pixel['blue']) < 200) {
                $visibleLogoPixelFound = true;

                break;
            }
        }
    }

    imagedestroy($thumbnail);

    expect($visibleLogoPixelFound)->toBeTrue();
});
