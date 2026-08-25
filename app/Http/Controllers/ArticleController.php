<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Article $article)
    {
        $article->load('images');

        return view('article.show', compact('article'));
    }

    public function create()
    {
        return view('article.create');
    }

    public function edit(Article $article)
    {
        if ((int) $article->user_id !== (int) Auth::id()) {
            abort(403);
        }

        return view('article.edit', compact('article'));
    }

    public function index(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $searchQuery = $request->input('q');
        $selectedCategoryId = (int) $request->input('category', 0);
        $selectedCategory = $selectedCategoryId > 0
            ? Category::query()->find($selectedCategoryId)
            : null;

        $articles = trim((string) $searchQuery) === ''
            ? Article::query()
                ->where('is_accepted', true)
                ->when($selectedCategory, function ($query) use ($selectedCategory) {
                    $query->whereBelongsTo($selectedCategory);
                })
                ->latest()
                ->paginate(12)
                ->withQueryString()
            : Article::search($searchQuery)
                ->query(function ($query) use ($selectedCategory) {
                    $query->where('is_accepted', true)
                        ->when($selectedCategory, function ($query) use ($selectedCategory) {
                            $query->whereBelongsTo($selectedCategory);
                        })
                        ->latest();
                })
                ->paginate(12)
                ->withQueryString();

        return view('article.index', [
            'articles' => $articles,
            'categories' => $categories,
            'searchQuery' => $searchQuery,
            'selectedCategory' => $selectedCategory?->getKey() ?? 0,
            'selectedCategoryName' => $selectedCategory?->name,
        ]);
    }

    public function destroy(Article $article)
{
    abort_if(auth()->id() !== $article->user_id, 403);

    $article->delete();

    return redirect()->route('article.index')->with('success', __('ui.article_deleted'));
}
}
