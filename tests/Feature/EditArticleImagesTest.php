<?php

use App\Jobs\AddArticleImageWatermark;
use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GenerateArticleCardThumbnail;
use App\Jobs\GoogleVisionSafeSearch;
use App\Livewire\ArticleCarousel;
use App\Livewire\Edit;
use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('google_id')->nullable();
        $table->string('avatar')->nullable();
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description');
        $table->decimal('price', 8, 2);
        $table->foreignId('category_id')->nullable();
        $table->foreignId('user_id')->nullable();
        $table->boolean('is_accepted')->nullable();
        $table->timestamps();
    });

    Schema::create('article_images', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
        $table->string('path');
        $table->string('watermarked_path')->nullable();
        $table->string('watermark_status')->default('pending');
        $table->timestamp('watermarked_at')->nullable();
        $table->string('card_path')->nullable();
        $table->string('processing_status')->default('pending');
        $table->json('vision_labels')->nullable();
        $table->string('vision_status')->default('pending');
        $table->timestamp('vision_analyzed_at')->nullable();
        $table->json('vision_safe_search')->nullable();
        $table->string('vision_safe_search_status')->default('pending');
        $table->timestamp('vision_safe_search_analyzed_at')->nullable();
        $table->unsignedInteger('position')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropAllTables();
});

it('renders placeholder in the card when the article has no uploaded images', function () {
    $article = Article::query()->create([
        'title' => 'Lampada vintage',
        'description' => 'Lampada in ottimo stato e perfettamente funzionante.',
        'price' => 45.50,
        'category_id' => null,
        'user_id' => null,
    ]);

    $html = view('components.card', ['article' => $article->load('images')])->render();

    expect($html)->toContain("https://picsum.photos/640/480?random={$article->id}");
});

it('renders the watermarked image in the card when the thumbnail is unavailable', function () {
    Storage::fake('public');

    $article = Article::query()->create([
        'title' => 'Tavolo in legno',
        'description' => 'Tavolo robusto da cucina con pochi segni di usura.',
        'price' => 120.00,
        'category_id' => null,
        'user_id' => null,
    ]);

    $image = ArticleImage::query()->create([
        'article_id' => $article->id,
        'path' => 'articles/'.$article->id.'/cover.png',
        'watermarked_path' => 'articles/'.$article->id.'/watermarked/cover.webp',
        'watermark_status' => ArticleImage::WATERMARK_READY,
        'watermarked_at' => now(),
        'position' => 1,
    ]);

    $html = view('components.card', ['article' => $article->load('images')])->render();

    expect($html)
        ->toContain(asset('storage/'.$image->watermarked_path))
        ->not->toContain(asset('storage/'.$image->path));
});

it('renders watermarked images in the article carousel', function () {
    $article = Article::query()->create([
        'title' => 'Tavolo in legno',
        'description' => 'Tavolo robusto da cucina con pochi segni di usura.',
        'price' => 120.00,
        'category_id' => null,
        'user_id' => null,
    ]);

    $image = ArticleImage::query()->create([
        'article_id' => $article->id,
        'path' => 'articles/'.$article->id.'/cover.png',
        'watermarked_path' => 'articles/'.$article->id.'/watermarked/cover.webp',
        'watermark_status' => ArticleImage::WATERMARK_READY,
        'watermarked_at' => now(),
        'position' => 1,
    ]);

    Livewire::test(ArticleCarousel::class, ['article' => $article])
        ->assertSet(
            'slides.0.src',
            asset('storage/'.$image->watermarked_path).'?v='.$image->updated_at->getTimestamp(),
        )
        ->assertSeeHtml('class="presto-carousel-image d-block w-100 h-100 object-fit-contain"');
});

it('shows inert edit and delete controls only to the article owner', function () {
    $owner = User::query()->create([
        'name' => 'Mario Rossi',
        'email' => 'mario-owner@example.com',
        'password' => Hash::make('password'),
    ]);

    $otherUser = User::query()->create([
        'name' => 'Luigi Verdi',
        'email' => 'luigi-other@example.com',
        'password' => Hash::make('password'),
    ]);

    $article = Article::query()->create([
        'title' => 'Sedia da giardino',
        'description' => 'Sedia da giardino in ottime condizioni.',
        'price' => 35.00,
        'category_id' => null,
        'user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get(route('article.show', $article))
        ->assertSuccessful()
        ->assertSee(route('article.edit', $article), false)
        ->assertSee('Modifica annuncio')
        ->assertSee('Elimina annuncio')
        ->assertSee('type="button"', false)
        ->assertSee('aria-label="Elimina annuncio"', false);

    $this->actingAs($otherUser)
        ->get(route('article.show', $article))
        ->assertSuccessful()
        ->assertDontSee('Modifica annuncio')
        ->assertDontSee('Elimina annuncio');
});

it('renders the generated card thumbnail when available', function () {
    $article = Article::query()->create([
        'title' => 'Tavolo in legno',
        'description' => 'Tavolo robusto da cucina con pochi segni di usura.',
        'price' => 120.00,
        'category_id' => null,
        'user_id' => null,
    ]);

    $image = ArticleImage::query()->create([
        'article_id' => $article->id,
        'path' => 'articles/'.$article->id.'/cover.png',
        'card_path' => 'articles/'.$article->id.'/cards/cover.webp',
        'processing_status' => ArticleImage::PROCESSING_READY,
        'position' => 1,
    ]);

    $html = view('components.card', ['article' => $article->load('images')])->render();

    expect($html)
        ->toContain(asset('storage/'.$image->card_path))
        ->not->toContain(asset('storage/'.$image->path));
});

it('removes one stored image at a time from edit view', function () {
    Storage::fake('public');

    $user = User::query()->create([
        'name' => 'Mario Rossi',
        'email' => 'mario-edit@example.com',
        'password' => Hash::make('password'),
    ]);

    $category = Category::query()->create(['name' => 'Casa e Giardinaggio']);

    $article = Article::query()->create([
        'title' => 'Mobile da salotto',
        'description' => 'Mobile moderno in ottime condizioni, colore noce.',
        'price' => 230.00,
        'category_id' => $category->id,
        'user_id' => $user->id,
    ]);

    Storage::disk('public')->put('articles/'.$article->id.'/one.png', 'img-one');
    Storage::disk('public')->put('articles/'.$article->id.'/watermarked/one.webp', 'watermarked-one');
    Storage::disk('public')->put('articles/'.$article->id.'/cards/one.webp', 'card-one');
    Storage::disk('public')->put('articles/'.$article->id.'/two.png', 'img-two');

    $firstImage = ArticleImage::query()->create([
        'article_id' => $article->id,
        'path' => 'articles/'.$article->id.'/one.png',
        'watermarked_path' => 'articles/'.$article->id.'/watermarked/one.webp',
        'watermark_status' => ArticleImage::WATERMARK_READY,
        'watermarked_at' => now(),
        'card_path' => 'articles/'.$article->id.'/cards/one.webp',
        'processing_status' => ArticleImage::PROCESSING_READY,
        'position' => 1,
    ]);

    $secondImage = ArticleImage::query()->create([
        'article_id' => $article->id,
        'path' => 'articles/'.$article->id.'/two.png',
        'position' => 2,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['article' => $article])
        ->call('removeStoredImage', $firstImage->id)
        ->assertSee('Elimina immagine');

    expect(ArticleImage::query()->whereKey($firstImage->id)->exists())->toBeFalse();
    expect(Storage::disk('public')->exists($firstImage->path))->toBeFalse();
    expect(Storage::disk('public')->exists($firstImage->watermarked_path))->toBeFalse();
    expect(Storage::disk('public')->exists($firstImage->card_path))->toBeFalse();

    $remainingImage = ArticleImage::query()->whereKey($secondImage->id)->first();

    expect($remainingImage)->not->toBeNull();
    expect($remainingImage->position)->toBe(1);
    expect(Storage::disk('public')->exists($secondImage->path))->toBeTrue();
});

it('queues card thumbnail generation for images added while editing', function () {
    Storage::fake('public');
    Queue::fake();

    $user = User::query()->create([
        'name' => 'Mario Rossi',
        'email' => 'mario-thumbnail@example.com',
        'password' => Hash::make('password'),
    ]);

    $category = Category::query()->create(['name' => 'Casa e Giardinaggio']);

    $article = Article::query()->create([
        'title' => 'Mobile da salotto',
        'description' => 'Mobile moderno in ottime condizioni, colore noce.',
        'price' => 230.00,
        'category_id' => $category->id,
        'user_id' => $user->id,
    ]);

    Livewire::actingAs($user)
        ->test(Edit::class, ['article' => $article])
        ->set('temporary_images', [
            UploadedFile::fake()->image('mobile.jpg', 800, 500),
        ])
        ->call('update');

    $articleImage = ArticleImage::query()->sole();

    expect($articleImage->processing_status)->toBe(ArticleImage::PROCESSING_PENDING);

    Queue::assertPushed(
        GenerateArticleCardThumbnail::class,
        fn (GenerateArticleCardThumbnail $job): bool => $job->articleImageId === $articleImage->id,
    );
    Queue::assertPushed(
        AddArticleImageWatermark::class,
        fn (AddArticleImageWatermark $job): bool => $job->articleImageId === $articleImage->id,
    );
    Queue::assertPushed(
        AnalyzeArticleImage::class,
        fn (AnalyzeArticleImage $job): bool => $job->articleImageId === $articleImage->id,
    );
    Queue::assertPushed(
        GoogleVisionSafeSearch::class,
        fn (GoogleVisionSafeSearch $job): bool => $job->articleImageId === $articleImage->id,
    );
});
