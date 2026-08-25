<?php

namespace App\Jobs;

use App\Contracts\ImageWatermarker;
use App\Models\ArticleImage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class AddArticleImageWatermark implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 300;

    /**
     * @var array<int, int>
     */
    public array $backoff = [5, 30, 60];

    public function __construct(public int $articleImageId) {}

    public function handle(ImageWatermarker $imageWatermarker): void
    {
        $articleImage = ArticleImage::query()->find($this->articleImageId);

        if ($articleImage === null) {
            return;
        }

        $disk = Storage::disk('public');

        if (
            $articleImage->watermark_status === ArticleImage::WATERMARK_READY
            && filled($articleImage->watermarked_path)
            && $disk->exists($articleImage->watermarked_path)
        ) {
            return;
        }

        $articleImage->update([
            'watermark_status' => ArticleImage::WATERMARK_PROCESSING,
        ]);

        if (! $disk->exists($articleImage->path)) {
            throw new RuntimeException("The source image [{$articleImage->path}] does not exist.");
        }

        $watermarkedPath = "articles/{$articleImage->article_id}/watermarked/{$articleImage->id}.webp";
        $watermarkedContents = $imageWatermarker->apply($disk->get($articleImage->path));

        if (! $disk->put($watermarkedPath, $watermarkedContents)) {
            throw new RuntimeException("The watermarked image [{$watermarkedPath}] could not be stored.");
        }

        $articleImage->update([
            'watermarked_path' => $watermarkedPath,
            'watermark_status' => ArticleImage::WATERMARK_READY,
            'watermarked_at' => now(),
        ]);

        GenerateArticleCardThumbnail::dispatch($articleImage->id);
    }

    public function uniqueId(): string
    {
        return (string) $this->articleImageId;
    }

    public function failed(?Throwable $exception): void
    {
        ArticleImage::query()
            ->whereKey($this->articleImageId)
            ->update(['watermark_status' => ArticleImage::WATERMARK_FAILED]);

        Log::error('Article image watermark generation failed.', [
            'article_image_id' => $this->articleImageId,
            'exception' => $exception,
        ]);
    }
}
