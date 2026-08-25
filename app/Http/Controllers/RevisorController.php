<?php

namespace App\Http\Controllers;

use App\Actions\ReopenArticleReview;
use App\Actions\ReviewSelectedArticles;
use App\Http\Requests\BulkReviewArticlesRequest;
use App\Mail\BecomeRevisor;
use App\Models\Article;
use App\Models\RevisionAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class RevisorController extends Controller
{
    public function index(): View
    {
        $articles = Article::query()
            ->select([
                'id',
                'title',
                'description',
                'price',
                'is_accepted',
                'category_id',
                'user_id',
                'created_at',
            ])
            ->with([
                'user:id,name',
                'category:id,name',
                'images',
            ])
            ->latest()
            ->paginate(12);
        $hasPendingArticles = $articles->getCollection()
            ->contains(fn (Article $article): bool => $article->is_accepted === null);

        return view('revisor.index', compact('articles', 'hasPendingArticles'));
    }

    public function show(Article $article): View
    {
        $article->loadMissing([
            'user:id,name',
            'category:id,name',
        ]);

        return view('revisor.show', compact('article'));
    }

    public function reviewSelected(
        BulkReviewArticlesRequest $request,
        ReviewSelectedArticles $reviewSelectedArticles
    ): RedirectResponse {
        $validated = $request->validated();
        $newStatus = $validated['decision'] === 'accept';
        $result = $reviewSelectedArticles->execute(
            $validated['articles'],
            $newStatus,
            $request->user()->getKey()
        );
        [$message, $messageType] = $this->reviewSelectedFeedback($result, $newStatus);

        return back()->with(compact('message', 'messageType'));
    }

    public function accept(Article $article): RedirectResponse
    {
        if (! $this->saveRevision($article, true)) {
            return redirect()->route('revisor.article.show', $article)->with([
                'message' => __('ui.article_reviewed_by_another'),
                'messageType' => 'danger',
            ]);
        }

        return redirect()->route('revisor_index')->with([
            'message' => __('ui.article_accepted_feedback', ['title' => $article->title]),
            'messageType' => 'success',
        ]);
    }

    public function reject(Article $article): RedirectResponse
    {
        if (! $this->saveRevision($article, false)) {
            return redirect()->route('revisor.article.show', $article)->with([
                'message' => __('ui.article_reviewed_by_another'),
                'messageType' => 'danger',
            ]);
        }

        return redirect()->route('revisor.article.show', $article)->with([
            'message' => __('ui.article_rejected_feedback', ['title' => $article->title]),
            'messageType' => 'danger',
        ]);
    }

    public function reopen(Article $article, ReopenArticleReview $reopenArticleReview): RedirectResponse
    {
        if (! $reopenArticleReview->execute($article)) {
            return back()->with([
                'message' => __('ui.article_already_pending'),
                'messageType' => 'danger',
            ]);
        }

        return redirect()->route('revisor.article.show', $article)->with([
            'message' => __('ui.article_reopened', ['title' => $article->title]),
            'messageType' => 'success',
        ]);
    }

    public function undo(RevisionAction $revisionAction): RedirectResponse
    {
        $revisionWasUndone = DB::transaction(function () use ($revisionAction): bool {
            $lastRevisionAction = RevisionAction::query()
                ->where('revisor_id', Auth::id())
                ->whereNull('undone_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $lastRevisionAction || ! $lastRevisionAction->is($revisionAction)) {
                return false;
            }

            $article = Article::query()
                ->lockForUpdate()
                ->find($lastRevisionAction->article_id);

            if (! $article) {
                return false;
            }

            $lastActionOnArticle = RevisionAction::query()
                ->where('article_id', $article->getKey())
                ->whereNull('undone_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (
                ! $lastActionOnArticle
                || ! $lastActionOnArticle->is($lastRevisionAction)
                || $article->is_accepted !== $lastRevisionAction->new_status
            ) {
                return false;
            }

            $article->setAccepted($lastRevisionAction->previous_status);
            $lastRevisionAction->update(['undone_at' => now()]);

            return true;
        });

        if (! $revisionWasUndone) {
            return back()->with([
                'message' => __('ui.review_undo_unavailable'),
                'messageType' => 'danger',
            ]);
        }

        return back()->with([
            'message' => __('ui.review_undone'),
            'messageType' => 'success',
        ]);
    }

    public function becomeRevisor()
    {
        $user = auth()->user();

        if ($user->is_revisor_requested) {
            return redirect()->back()->with('errorRequest', __('ui.revisor_request_already_sent'));
        }

        $user->is_revisor_requested = true;
        $user->save();

        Mail::to('admin@presto.it')->send(new BecomeRevisor($user));

        return redirect()->route('homepage')->with('message', __('ui.revisor_request_sent'));
    }

    public function makeRevisor(User $user)
    {
        Artisan::call('app:make-user-revisor', ['email' => $user->email]);
        $user->is_revisor_requested = false;
        $user->save();

        return back();
    }

    private function saveRevision(Article $article, bool $newStatus): bool
    {
        return DB::transaction(function () use ($article, $newStatus): bool {
            $articleToReview = Article::query()
                ->lockForUpdate()
                ->findOrFail($article->getKey());

            if ($articleToReview->is_accepted !== null) {
                return false;
            }

            $articleToReview->revisionActions()->create([
                'revisor_id' => Auth::id(),
                'previous_status' => $articleToReview->is_accepted,
                'new_status' => $newStatus,
            ]);

            $articleToReview->setAccepted($newStatus);

            return true;
        });
    }

    /**
     * @param  array{reviewed: int, skipped: int}  $result
     * @return array{string, string}
     */
    private function reviewSelectedFeedback(array $result, bool $newStatus): array
    {
        if ($result['reviewed'] === 0) {
            return [__('ui.no_reviewable_articles'), 'danger'];
        }

        $message = trans_choice(
            $newStatus ? 'ui.reviewed_articles_accepted' : 'ui.reviewed_articles_rejected',
            $result['reviewed'],
            ['count' => $result['reviewed']]
        );

        if ($result['skipped'] > 0) {
            $message .= ' '.trans_choice(
                'ui.reviewed_articles_skipped',
                $result['skipped'],
                ['count' => $result['skipped']]
            );
        }

        return [$message, 'success'];
    }
}
