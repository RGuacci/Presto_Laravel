<?php

namespace App\Actions;

use App\Models\Article;
use App\Models\RevisionAction;
use Illuminate\Support\Facades\DB;

class ReopenArticleReview
{
    public function execute(Article $article): bool
    {
        return DB::transaction(function () use ($article): bool {
            $articleToReopen = Article::query()
                ->lockForUpdate()
                ->findOrFail($article->getKey());

            if ($articleToReopen->is_accepted === null) {
                return false;
            }

            $lastRevisionAction = RevisionAction::query()
                ->where('article_id', $articleToReopen->getKey())
                ->whereNull('undone_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($lastRevisionAction?->new_status === $articleToReopen->is_accepted) {
                $lastRevisionAction->update(['undone_at' => now()]);
            }

            $articleToReopen->setAccepted(null);

            return true;
        }, 3);
    }
}
