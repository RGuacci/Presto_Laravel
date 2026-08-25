<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GoogleVisionSafeSearch;
use App\Models\ArticleImage;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

#[Signature('articles:analyze-images {--retry-failed : Retry image analyses that previously failed}')]
#[Description('Queue Google Vision label and SafeSearch detection for article images without an analysis')]
class QueueMissingArticleImageAnalyses extends Command
{
    public function handle(): int
    {
        $statuses = [ArticleImage::VISION_PENDING];

        if ($this->option('retry-failed')) {
            $statuses[] = ArticleImage::VISION_FAILED;
        }

        $query = ArticleImage::query()
            ->where(function (Builder $query) use ($statuses): void {
                $query
                    ->whereIn('vision_status', $statuses)
                    ->orWhereIn('vision_safe_search_status', $statuses);
            })
            ->orderBy('id');

        $imageCount = $query->count();

        if ($imageCount === 0) {
            $this->components->info('No article images need Google Vision analysis.');

            return self::SUCCESS;
        }

        $queuedJobCount = 0;
        $progressBar = $this->output->createProgressBar($imageCount);
        $progressBar->start();

        $query
            ->select(['id', 'vision_status', 'vision_safe_search_status'])
            ->chunkById(100, function (Collection $articleImages) use (&$queuedJobCount, $progressBar, $statuses): void {
                foreach ($articleImages as $articleImage) {
                    if (in_array($articleImage->vision_status, $statuses, true)) {
                        $articleImage->update([
                            'vision_labels' => null,
                            'vision_status' => ArticleImage::VISION_PENDING,
                            'vision_analyzed_at' => null,
                        ]);

                        AnalyzeArticleImage::dispatch($articleImage->id);
                        $queuedJobCount++;
                    }

                    if (in_array($articleImage->vision_safe_search_status, $statuses, true)) {
                        $articleImage->update([
                            'vision_safe_search' => null,
                            'vision_safe_search_status' => ArticleImage::VISION_PENDING,
                            'vision_safe_search_analyzed_at' => null,
                        ]);

                        GoogleVisionSafeSearch::dispatch($articleImage->id);
                        $queuedJobCount++;
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);
        $this->components->info("Queued {$queuedJobCount} Google Vision analyses for {$imageCount} images.");

        return self::SUCCESS;
    }
}
