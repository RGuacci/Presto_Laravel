<?php

namespace App\Jobs;

use App\Contracts\ImageSafeSearchAnalyzer;
use App\Models\ArticleImage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GoogleVisionSafeSearch implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $timeout = 60;

    public int $uniqueFor = 600;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60, 180];

    public function __construct(public int $articleImageId) {}

    public function handle(ImageSafeSearchAnalyzer $safeSearchAnalyzer): void
    {
        $articleImage = ArticleImage::query()->find($this->articleImageId);

        if (
            $articleImage === null
            || $articleImage->vision_safe_search_status === ArticleImage::VISION_READY
        ) {
            return;
        }

        $articleImage->update([
            'vision_safe_search_status' => ArticleImage::VISION_PROCESSING,
        ]);

        $disk = Storage::disk('public');

        if (! $disk->exists($articleImage->path)) {
            throw new RuntimeException("The source image [{$articleImage->path}] does not exist.");
        }

        $articleImage->update([
            'vision_safe_search' => $safeSearchAnalyzer->analyze($disk->get($articleImage->path)),
            'vision_safe_search_status' => ArticleImage::VISION_READY,
            'vision_safe_search_analyzed_at' => now(),
        ]);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new RateLimited('google-vision')];
    }

    public function uniqueId(): string
    {
        return (string) $this->articleImageId;
    }

    public function failed(?Throwable $exception): void
    {
        ArticleImage::query()
            ->whereKey($this->articleImageId)
            ->update(['vision_safe_search_status' => ArticleImage::VISION_FAILED]);

        Log::error('Google Vision SafeSearch article image analysis failed.', [
            'article_image_id' => $this->articleImageId,
            'exception' => $exception,
        ]);
    }
}
