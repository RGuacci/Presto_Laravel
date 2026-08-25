<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description');
        $table->decimal('price', 8, 2);
        $table->foreignId('category_id')->nullable();
        $table->foreignId('user_id')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropAllTables();
});

it('restituisce gli articoli attesi tramite scout', function () {
    $category = Category::query()->create([
        'name' => 'Elettronica',
    ]);

    $user = User::factory()->create();

    $matchingArticle = Article::query()->create([
        'title' => 'Lampada da scrivania Scout',
        'description' => 'Annuncio perfetto per verificare la ricerca tramite Scout',
        'price' => 49.90,
        'category_id' => $category->getKey(),
        'user_id' => $user->getKey(),
    ]);

    Article::query()->create([
        'title' => 'Sedia da ufficio',
        'description' => 'Questo articolo non deve comparire nei risultati',
        'price' => 79.90,
        'category_id' => $category->getKey(),
        'user_id' => $user->getKey(),
    ]);

    $results = Article::search('Scout')->get();

    expect($results)
        ->toHaveCount(1)
        ->and($results->first()->is($matchingArticle))->toBeTrue();
});
