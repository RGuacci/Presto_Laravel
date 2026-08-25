<?php

namespace App\Livewire;

use App\Jobs\AddArticleImageWatermark;
use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GenerateArticleCardThumbnail;
use App\Jobs\RemoveFaces;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
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

    #[Validate([
        'temporary_images.*' => 'image|max:2048', // max 2MB per immagine
    ])]
    public $temporary_images = [];

    public Article $article;

    public function mount(Article $article)
    {
        $this->article = $article->load('images');
        $this->title = $article->title;
        $this->description = $article->description;
        $this->price = $article->price;
        $this->category = $article->category_id;
    }

    // Rimuove un'immagine temporanea non ancora salvata
    public function removeTemporaryImage(int $key): void
    {
        unset($this->temporary_images[$key]);
        $this->temporary_images = array_values($this->temporary_images);
    }

    public function removeStoredImage(int $imageId): void
    {
        $image = $this->article->images()->whereKey($imageId)->first();

        if ($image === null) {
            return;
        }

        Storage::disk('public')->delete(array_filter([
            $image->path,
            $image->watermarked_path,
            $image->card_path,
        ]));
        $image->delete();

        $this->article->images()
            ->orderBy('position')
            ->get()
            ->values()
            ->each(function ($image, int $index): void {
                $image->update(['position' => $index + 1]);
            });

        $this->article->load('images');
    }

    public function update()
    {
        $this->validate();

        $this->article->update([
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category_id' => $this->category,
        ]);

        if (! empty($this->temporary_images)) {
            $lastPosition = $this->article->images()->max('position') ?? 0;

            foreach ($this->temporary_images as $image) {
                $lastPosition++;

                $path = $image->store("articles/{$this->article->id}", 'public');

                $articleImage = $this->article->images()->create([
                    'path' => $path,
                    'position' => $lastPosition,
                ]);

                AddArticleImageWatermark::dispatch($articleImage->id)->afterCommit();
                $imageId = $articleImage->id;

                // Catena in background: prima censura, poi miniatura, poi analisi
                DB::afterCommit(function () use ($imageId) {
                    RemoveFaces::withChain([
                        new GenerateArticleCardThumbnail($imageId),
                        new AnalyzeArticleImage($imageId),
                    ])->dispatch($imageId);
                });
            }
        }

        session()->flash('success', __('ui.article_updated_success'));

        return redirect()->route('article.index');
    }

    public function render()
    {
        return view('livewire.edit', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
