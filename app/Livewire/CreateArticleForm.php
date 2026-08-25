<?php

namespace App\Livewire;

use App\Jobs\AddArticleImageWatermark;
use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GenerateArticleCardThumbnail;
use App\Jobs\GoogleVisionSafeSearch;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Jobs\RemoveFaces;

class CreateArticleForm extends Component
{
    use WithFileUploads;

    #[Validate('required|min:5')]
    public $title;

    #[Validate('required|min:10')]
    public $description;

    #[Validate('required|numeric')]
    public $price;

    #[Validate('required')]
    public $category;

    public array $images = [];

    public $article;

    public function removeImage(int $index): void
    {
        if (! isset($this->images[$index])) {
            return;
        }

        unset($this->images[$index]);
        $this->images = array_values($this->images);
    }

    public function save()
    {
        $this->validate();

        $this->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'mimes:jpg,jpeg,png,webp,avif|max:2048',
        ]);

        DB::transaction(function (): void {
            $this->article = Article::create([
                'title' => $this->title,
                'description' => $this->description,
                'price' => $this->price,
                'category_id' => $this->category,
                'user_id' => Auth::id(),
            ]);

            foreach ($this->images as $index => $image) {
                $storedPath = $image->store("articles/{$this->article->id}", 'public');

                $articleImage = $this->article->images()->create([
                    'path' => $storedPath,
                    'position' => $index + 1,
                ]);

                AddArticleImageWatermark::dispatch($articleImage->id)->afterCommit();
                $imageId = $articleImage->id;
                DB::afterCommit(function () use ($imageId) {
                    RemoveFaces::withChain([
                        new GenerateArticleCardThumbnail($imageId),
                    ])->dispatch($imageId);
                });
            }
        });

        $this->cleanForm();

        session()->flash('success', __('ui.article_created_success'));

        return redirect()->route('article.index');
    }

    public function render()
    {
        $categories = Category::orderBy('name')->get();

        return view('livewire.create-article-form', compact('categories'));
    }

    private function cleanForm()
    {
        $this->title = '';
        $this->description = '';
        $this->category = '';
        $this->price = '';
        $this->images = [];
    }
}
