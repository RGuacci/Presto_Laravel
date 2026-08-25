<?php

namespace App\Actions;

use App\Models\Article;
use Illuminate\Support\Facades\DB;

class ReviewSelectedArticles
{
    /**
     * @param  list<int>  $articleIds
     * @return array{reviewed: int, skipped: int}
     */
    public function execute(array $articleIds, bool $newStatus, int $revisorId): array
    {
        return DB::transaction(function () use ($articleIds, $newStatus, $revisorId): array {
            $articles = Article::query()
                ->whereKey($articleIds)
                ->lockForUpdate()
                ->get();

            $reviewedCount = 0;
            $skippedCount = count($articleIds) - $articles->count();

            foreach ($articles as $article) {
                if ($article->is_accepted !== null) {
                    $skippedCount++;

                    continue;
                }

                $article->revisionActions()->create([
                    'revisor_id' => $revisorId,
                    'previous_status' => $article->is_accepted,
                    'new_status' => $newStatus,
                ]);

                $article->setAccepted($newStatus);
                $reviewedCount++;
            }

            return [
                'reviewed' => $reviewedCount,
                'skipped' => $skippedCount,
            ];
        });
    }
}
