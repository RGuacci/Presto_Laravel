<?php

use App\Jobs\AddArticleImageWatermark;
use App\Jobs\AnalyzeArticleImage;
use App\Jobs\GenerateArticleCardThumbnail;
use App\Jobs\GoogleVisionSafeSearch;
use App\Livewire\CreateArticleForm;
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

it('creates an article with multiple images and stores them', function () {
    Storage::fake('public');
    Queue::fake();
    $pixelPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlH0x8AAAAASUVORK5CYII=');

    $user = User::query()->create([
        'name' => 'Mario Rossi',
        'email' => 'mario@example.com',
        'password' => Hash::make('password'),
    ]);
    $category = Category::query()->create(['name' => 'Elettronica']);

    $images = [
        UploadedFile::fake()->createWithContent('first.png', $pixelPng),
        UploadedFile::fake()->createWithContent('second.png', $pixelPng),
    ];

    Livewire::actingAs($user)
        ->test(CreateArticleForm::class)
        ->set('title', 'Bicicletta da citta praticamente nuova')
        ->set('description', 'Bicicletta usata pochissimo, tenuta sempre in garage e perfettamente funzionante.')
        ->set('price', 299.99)
        ->set('category', $category->id)
        ->set('images', $images)
        ->call('save');

    $article = Article::query()->with('images')->first();

    expect($article)->not->toBeNull();
    expect($article->images)->toHaveCount(2);

    foreach ($article->images as $image) {
        expect(Storage::disk('public')->exists($image->path))->toBeTrue();
        expect($image->watermarked_path)->toBeNull();
        expect($image->watermark_status)->toBe(ArticleImage::WATERMARK_PENDING);
        expect($image->card_path)->toBeNull();
        expect($image->processing_status)->toBe('pending');
        expect($image->vision_status)->toBe('pending');
        expect($image->vision_safe_search_status)->toBe('pending');
    }

    Queue::assertPushed(GenerateArticleCardThumbnail::class, 2);
    Queue::assertPushed(AddArticleImageWatermark::class, 2);
    Queue::assertPushed(AnalyzeArticleImage::class, 2);
    Queue::assertPushed(GoogleVisionSafeSearch::class, 2);
});

it('removes one selected image at a time before save', function () {
    $pixelPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlH0x8AAAAASUVORK5CYII=');

    Livewire::test(CreateArticleForm::class)
        ->set('images', [
            UploadedFile::fake()->createWithContent('one.png', $pixelPng),
            UploadedFile::fake()->createWithContent('two.png', $pixelPng),
            UploadedFile::fake()->createWithContent('three.png', $pixelPng),
        ])
        ->call('removeImage', 1)
        ->assertSet('images', function (array $images): bool {
            return count($images) === 2;
        });
});
