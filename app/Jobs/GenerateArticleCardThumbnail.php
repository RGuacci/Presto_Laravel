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

class GenerateArticleCardThumbnail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const WIDTH = 300;

    public const HEIGHT = 300;

    public const QUALITY = 82;

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

        if (! $disk->exists($articleImage->path)) {
            throw new RuntimeException("The source image [{$articleImage->path}] does not exist.");
        }

        if (! extension_loaded('gd') || ! function_exists('imagewebp')) {
            throw new RuntimeException('The GD extension with WebP support is required.');
        }

        $sourceImage = imagecreatefromstring($disk->get($articleImage->path));

        if ($sourceImage === false) {
            throw new RuntimeException("The source image [{$articleImage->path}] could not be decoded.");
        }

        $thumbnail = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        if ($thumbnail === false) {
            imagedestroy($sourceImage);

            throw new RuntimeException('The card thumbnail canvas could not be created.');
        }

        try {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 0, 0, 0, 127);
            imagefill($thumbnail, 0, 0, $transparent);

            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);
            $cropSize = min($sourceWidth, $sourceHeight);
            $sourceX = intdiv($sourceWidth - $cropSize, 2);
            $sourceY = intdiv($sourceHeight - $cropSize, 2);

            $wasResampled = imagecopyresampled(
                $thumbnail,
                $sourceImage,
                0,
                0,
                $sourceX,
                $sourceY,
                self::WIDTH,
                self::HEIGHT,
                $cropSize,
                $cropSize,
            );

            if (! $wasResampled) {
                throw new RuntimeException('The source image could not be cropped.');
            }

            $thumbnailContents = $imageWatermarker->apply($this->encodeThumbnail($thumbnail));
            $cardPath = "articles/{$articleImage->article_id}/cards/{$articleImage->id}.webp";

            if (! $disk->put($cardPath, $thumbnailContents)) {
                throw new RuntimeException("The card thumbnail [{$cardPath}] could not be stored.");
            }

            $articleImage->update([
                'card_path' => $cardPath,
                'processing_status' => ArticleImage::PROCESSING_READY,
            ]);
        } finally {
            imagedestroy($thumbnail);
            imagedestroy($sourceImage);
        }
    }

    public function uniqueId(): string
    {
        return (string) $this->articleImageId;
    }

    public function failed(?Throwable $exception): void
    {
        ArticleImage::query()
            ->whereKey($this->articleImageId)
            ->update(['processing_status' => ArticleImage::PROCESSING_FAILED]);

        Log::error('Article card thumbnail generation failed.', [
            'article_image_id' => $this->articleImageId,
            'exception' => $exception,
        ]);
    }

    private function encodeThumbnail(\GdImage $thumbnail): string
    {
        ob_start();

        try {
            if (! imagewebp($thumbnail, null, self::QUALITY)) {
                throw new RuntimeException('The card thumbnail could not be encoded as WebP.');
            }

            $contents = ob_get_contents();

            if (! is_string($contents) || $contents === '') {
                throw new RuntimeException('The encoded card thumbnail is empty.');
            }

            return $contents;
        } finally {
            ob_end_clean();
        }
    }
}
