<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleSearch extends Component
{
    use WithPagination;

     protected string $paginationTheme = 'bootstrap';

    #[Url(as: 'q', history: true)]
    public string $q = '';

    #[Url(as: 'category', history: true)]
    public int $selectedCategory = 0;

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory(): void
    {
        $this->resetPage();
    }

    public function selectCategory(int $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function render(): View
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategory = $categories->firstWhere('id', $this->selectedCategory);

        $articles = trim($this->q) === ''
            ? Article::query()
                ->with('images')
                ->where('is_accepted', true)
                ->when($selectedCategory, function ($query) use ($selectedCategory) {
                    $query->whereBelongsTo($selectedCategory);
                })
                ->latest()
                ->paginate(12)
            : Article::search($this->q)
                ->where('is_accepted', true)
                ->query(function ($query) use ($selectedCategory) {
                    $query->with('images');

                    $query->when($selectedCategory, function ($query) use ($selectedCategory) {
                        $query->whereBelongsTo($selectedCategory);
                    });

                    $query->latest();
                })
                ->paginate(12);

        return view('article.article-search', [
            'articles' => $articles,
            'categories' => $categories,
            'selectedCategoryName' => $selectedCategory?->name,
        ]);
    }
}
