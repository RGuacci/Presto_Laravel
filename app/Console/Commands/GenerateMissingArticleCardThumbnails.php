<?php

namespace App\Console\Commands;

use App\Jobs\GenerateArticleCardThumbnail;
use App\Models\ArticleImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

#[Signature('articles:generate-card-thumbnails {--force : Regenerate thumbnails that are already available}')]
#[Description('Queue the generation of card thumbnails for article images')]
class GenerateMissingArticleCardThumbnails extends Command
{
    public function handle(): int
    {
        $query = ArticleImage::query()
            ->when(! $this->option('force'), function (Builder $query): void {
                $query->whereNull('card_path');
            })
            ->orderBy('id');

        $imageCount = $query->count();

        if ($imageCount === 0) {
            $this->components->info('No article card thumbnails need to be generated.');

            return self::SUCCESS;
        }

        $queuedCount = 0;
        $progressBar = $this->output->createProgressBar($imageCount);
        $progressBar->start();

        $query->select('id')->chunkById(100, function (Collection $articleImages) use (&$queuedCount, $progressBar): void {
            foreach ($articleImages as $articleImage) {
                $articleImage->update([
                    'processing_status' => ArticleImage::PROCESSING_PENDING,
                ]);

                GenerateArticleCardThumbnail::dispatch($articleImage->id);

                $queuedCount++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);
        $this->components->info("Queued {$queuedCount} article card thumbnails.");

        return self::SUCCESS;
    }
}
