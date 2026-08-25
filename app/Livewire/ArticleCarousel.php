<?php

namespace App\Livewire;

use App\Models\Article;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ArticleCarousel extends Component
{
    public Article $article;

    /**
     * @var array<int, array{src: string, alt: string}>
     */
    public array $slides = [];

    public int $currentSlide = 0;

    public function mount(Article $article): void
    {
        $this->article = $article->loadMissing('images');
        $this->slides = $this->buildSlides();
    }

    public function nextSlide(): void
    {
        if ($this->slides === []) {
            return;
        }

        $this->currentSlide = ($this->currentSlide + 1) % count($this->slides);
    }

    public function prevSlide(): void
    {
        if ($this->slides === []) {
            return;
        }

        $this->currentSlide = ($this->currentSlide - 1 + count($this->slides)) % count($this->slides);
    }

    public function goToSlide(int $slideIndex): void
    {
        if (! isset($this->slides[$slideIndex])) {
            return;
        }

        $this->currentSlide = $slideIndex;
    }

    public function render()
    {
        return view('livewire.article-carousel');
    }

    /**
     * @return array<int, array{src: string, alt: string}>
     */
    private function buildSlides(): array
    {
        $slides = $this->article->images
            ->values()
            ->map(function ($image, int $index): array {
                return [
                    'src' => asset('storage/'.$image->displayPath()).'?v='.$image->updated_at->getTimestamp(),
                    'alt' => __('ui.carousel_image_alt', [
                        'title' => $this->article->title,
                        'number' => $index + 1,
                    ]),
                ];
            })
            ->all();

        if ($slides !== []) {
            return $slides;
        }

        return [[
            'src' => 'https://placehold.co/1200x800?text='.urlencode(__('ui.no_image_available')),
            'alt' => __('ui.no_image_available'),
        ]];
    }
}
