<?php

use App\Models\Article;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    config()->set('scout.driver', 'collection');

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

it('mostra gli articoli trovati da scout nella pagina indice', function () {
    $matchingArticle = Article::query()->create([
        'title' => 'Lampada Scout',
        'description' => 'Annuncio che deve essere trovato',
        'price' => 25,
        'category_id' => null,
        'user_id' => null,
    ]);
    $matchingArticle->setAccepted(true);

    $unrelatedArticle = Article::query()->create([
        'title' => 'Sedia ufficio',
        'description' => 'Annuncio che non deve comparire',
        'price' => 40,
        'category_id' => null,
        'user_id' => null,
    ]);
    $unrelatedArticle->setAccepted(true);

    $rejectedArticle = Article::query()->create([
        'title' => 'Tavolo Scout rifiutato',
        'description' => 'Annuncio rifiutato che non deve comparire',
        'price' => 55,
        'category_id' => null,
        'user_id' => null,
    ]);
    $rejectedArticle->setAccepted(false);

    Article::query()->create([
        'title' => 'Divano Scout in revisione',
        'description' => 'Annuncio in revisione che non deve comparire',
        'price' => 75,
        'category_id' => null,
        'user_id' => null,
    ]);

    $this->get(route('article.index', ['q' => 'Scout']))
        ->assertOk()
        ->assertSee('Lampada Scout')
        ->assertDontSee('Sedia ufficio')
        ->assertDontSee('Tavolo Scout rifiutato')
        ->assertDontSee('Divano Scout in revisione');
});

it('mostra soltanto gli articoli accettati nel catalogo pubblico', function () {
    $acceptedArticle = Article::query()->create([
        'title' => 'Articolo pubblico accettato',
        'description' => 'Annuncio visibile nel catalogo',
        'price' => 25,
        'category_id' => null,
        'user_id' => null,
    ]);
    $acceptedArticle->setAccepted(true);

    $rejectedArticle = Article::query()->create([
        'title' => 'Articolo pubblico rifiutato',
        'description' => 'Annuncio non visibile nel catalogo',
        'price' => 40,
        'category_id' => null,
        'user_id' => null,
    ]);
    $rejectedArticle->setAccepted(false);

    Article::query()->create([
        'title' => 'Articolo pubblico in revisione',
        'description' => 'Annuncio non ancora revisionato',
        'price' => 60,
        'category_id' => null,
        'user_id' => null,
    ]);

    $this->get(route('article.index'))
        ->assertOk()
        ->assertSee('Articolo pubblico accettato')
        ->assertDontSee('Articolo pubblico rifiutato')
        ->assertDontSee('Articolo pubblico in revisione');
});
