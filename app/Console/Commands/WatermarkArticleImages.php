<?php

namespace App\Console\Commands;

use App\Jobs\AddArticleImageWatermark;
use App\Models\ArticleImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

#[Signature('articles:watermark-images {--force : Regenerate watermarks that are already available}')]
#[Description('Queue watermark generation for article images')]
class WatermarkArticleImages extends Command
{
    public function handle(): int
    {
        $query = ArticleImage::query()
            ->when(! $this->option('force'), function (Builder $query): void {
                $query->where('watermark_status', '!=', ArticleImage::WATERMARK_READY);
            })
            ->orderBy('id');

        $imageCount = $query->count();

        if ($imageCount === 0) {
            $this->components->info('No article images need a watermark.');

            return self::SUCCESS;
        }

        $queuedCount = 0;
        $progressBar = $this->output->createProgressBar($imageCount);
        $progressBar->start();

        $query->select('id')->chunkById(100, function (Collection $articleImages) use (&$queuedCount, $progressBar): void {
            foreach ($articleImages as $articleImage) {
                $articleImage->update([
                    'watermark_status' => ArticleImage::WATERMARK_PENDING,
                    'watermarked_at' => null,
                ]);

                AddArticleImageWatermark::dispatch($articleImage->id);

                $queuedCount++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);
        $this->components->info("Queued {$queuedCount} article image watermarks.");

        return self::SUCCESS;
    }
}
