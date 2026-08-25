<?php

use App\Models\Article;
use App\Models\ArticleImage;
use App\Models\Category;
use App\Models\RevisionAction;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    app()->setLocale('it');
    $this->withSession(['locale' => 'it']);

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
        $table->string('google_id')->nullable();
        $table->string('avatar')->nullable();
        $table->boolean('is_revisor')->default(false);
        $table->rememberToken();
        $table->timestamps();
    });

    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description');
        $table->decimal('price', 8, 2);
        $table->foreignId('category_id')->nullable();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->boolean('is_accepted')->nullable();
        $table->timestamps();
    });

    Schema::create('article_images', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')->constrained()->cascadeOnDelete();
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

    Schema::create('revision_actions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('article_id')->constrained()->cascadeOnDelete();
        $table->foreignId('revisor_id')->constrained('users')->cascadeOnDelete();
        $table->boolean('previous_status')->nullable();
        $table->boolean('new_status');
        $table->timestamp('undone_at')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropAllTables();
});

function createPendingArticleForRevisionTest(User $author, string $title): Article
{
    return Article::query()->create([
        'title' => $title,
        'description' => 'Descrizione di prova',
        'price' => 25,
        'category_id' => Category::query()->firstOrCreate(['name' => 'Categoria di prova'])->getKey(),
        'user_id' => $author->getKey(),
    ]);
}

it('mostra nel catalogo tutti gli articoli con il loro stato di revisione', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $pendingArticle = createPendingArticleForRevisionTest($author, 'Articolo ancora da revisionare');
    $acceptedArticle = createPendingArticleForRevisionTest($author, 'Articolo già accettato');
    $rejectedArticle = createPendingArticleForRevisionTest($author, 'Articolo già rifiutato');

    $acceptedArticle->setAccepted(true);
    $rejectedArticle->setAccepted(false);

    ArticleImage::query()->create([
        'article_id' => $pendingArticle->id,
        'path' => "articles/{$pendingArticle->id}/bicycle.jpg",
        'vision_labels' => [
            ['description' => 'Bicycle', 'score' => 0.94],
            ['description' => 'Wheel', 'score' => 0.87],
        ],
        'vision_status' => ArticleImage::VISION_READY,
        'vision_analyzed_at' => now(),
        'vision_safe_search' => [
            'adult' => 'very_unlikely',
            'spoof' => 'unlikely',
            'medical' => 'very_unlikely',
            'violence' => 'unlikely',
            'racy' => 'possible',
        ],
        'vision_safe_search_status' => ArticleImage::VISION_READY,
        'vision_safe_search_analyzed_at' => now(),
        'position' => 1,
    ]);

    $this->actingAs($revisor)
        ->get(route('revisor_index'))
        ->assertSuccessful()
        ->assertSee('Catalogo revisioni')
        ->assertSee('presto-nav-link-active', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee('<table class="presto-revisor-table">', false)
        ->assertSee('<th scope="col">Articolo</th>', false)
        ->assertSee('<th scope="col">Stato</th>', false)
        ->assertDontSee('presto-revisor-table-action', false)
        ->assertSee('data-review-url="'.route('revisor.article.show', $pendingArticle).'"', false)
        ->assertSee('Rifiuta selezionati')
        ->assertSee('Accetta selezionati')
        ->assertSee('aria-label="Seleziona '.$pendingArticle->title.'"', false)
        ->assertDontSee('aria-label="Seleziona '.$acceptedArticle->title.'"', false)
        ->assertDontSee('aria-label="Seleziona '.$rejectedArticle->title.'"', false)
        ->assertSee($pendingArticle->title)
        ->assertSee($acceptedArticle->title)
        ->assertSee($rejectedArticle->title)
        ->assertSee('Da revisionare')
        ->assertSee('Accettato')
        ->assertSee('Rifiutato')
        ->assertSee($pendingArticle->created_at->format('d/m/Y H:i'))
        ->assertSee(route('revisor.article.show', $pendingArticle), false)
        ->assertSee(route('revisor.article.show', $acceptedArticle), false)
        ->assertSee(route('revisor.article.show', $rejectedArticle), false);
});

it('nasconde le indicazioni automatiche e mostra le immagini nel carosello', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Bicicletta da revisionare');

    foreach (range(1, 4) as $position) {
        ArticleImage::query()->create([
            'article_id' => $article->id,
            'path' => "articles/{$article->id}/image-{$position}.jpg",
            'vision_labels' => [
                ['description' => 'Etichetta da non mostrare', 'score' => 0.94],
            ],
            'vision_status' => ArticleImage::VISION_READY,
            'vision_analyzed_at' => now(),
            'vision_safe_search' => [
                'adult' => 'very_unlikely',
                'spoof' => 'unlikely',
                'medical' => 'very_unlikely',
                'violence' => 'unlikely',
                'racy' => 'possible',
            ],
            'vision_safe_search_status' => ArticleImage::VISION_READY,
            'vision_safe_search_analyzed_at' => now(),
            'position' => $position,
        ]);
    }

    $this->actingAs($revisor)
        ->get(route('revisor.article.show', $article))
        ->assertSuccessful()
        ->assertSee('id="revisorCarousel"', false)
        ->assertSee('class="presto-revisor-photo w-100 object-fit-cover"', false)
        ->assertSee('class="presto-revisor-watermark"', false)
        ->assertDontSee('title="Adulti"', false)
        ->assertDontSee('title="Violenza"', false)
        ->assertDontSee('title="Satira"', false)
        ->assertDontSee('title="Medico"', false)
        ->assertDontSee('title="Provocatorio"', false)
        ->assertDontSee('Indicazione AI Google Vision')
        ->assertDontSee('Etichetta da non mostrare')
        ->assertDontSee('Controllo contenuti SafeSearch')
        ->assertDontSee('Risultato automatico e orientativo');
});

it('accetta più articoli selezionati e ignora quelli già revisionati', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $firstPendingArticle = createPendingArticleForRevisionTest($author, 'Primo articolo selezionato');
    $secondPendingArticle = createPendingArticleForRevisionTest($author, 'Secondo articolo selezionato');
    $alreadyReviewedArticle = createPendingArticleForRevisionTest($author, 'Articolo già controllato');

    $alreadyReviewedArticle->setAccepted(false);

    $this->actingAs($revisor)
        ->from(route('revisor_index'))
        ->patch(route('revisor.articles.review_selected'), [
            'articles' => [
                $firstPendingArticle->getKey(),
                $secondPendingArticle->getKey(),
                $alreadyReviewedArticle->getKey(),
            ],
            'decision' => 'accept',
        ])
        ->assertRedirect(route('revisor_index'))
        ->assertSessionHas('message', 'Hai accettato 2 articoli. 1 era già stato revisionato.');

    expect($firstPendingArticle->fresh()->is_accepted)->toBeTrue()
        ->and($secondPendingArticle->fresh()->is_accepted)->toBeTrue()
        ->and($alreadyReviewedArticle->fresh()->is_accepted)->toBeFalse()
        ->and(RevisionAction::query()->where('revisor_id', $revisor->getKey())->count())->toBe(2)
        ->and(RevisionAction::query()->where('new_status', true)->count())->toBe(2);
});

it('rifiuta un singolo articolo selezionato', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo singolo da rifiutare');

    $this->actingAs($revisor)
        ->from(route('revisor_index'))
        ->patch(route('revisor.articles.review_selected'), [
            'articles' => [$article->getKey()],
            'decision' => 'reject',
        ])
        ->assertRedirect(route('revisor_index'))
        ->assertSessionHas('message', 'Hai rifiutato un articolo.');

    expect($article->fresh()->is_accepted)->toBeFalse()
        ->and(RevisionAction::query()->count())->toBe(1)
        ->and(RevisionAction::query()->firstOrFail()->new_status)->toBeFalse();
});

it('richiede almeno un articolo per la revisione dalla tabella', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo non selezionato');

    $this->actingAs($revisor)
        ->from(route('revisor_index'))
        ->patch(route('revisor.articles.review_selected'), [
            'articles' => [],
            'decision' => 'accept',
        ])
        ->assertRedirect(route('revisor_index'))
        ->assertSessionHasErrors('articles');

    expect($article->fresh()->is_accepted)->toBeNull()
        ->and(RevisionAction::query()->count())->toBe(0);
});

it('apre il dettaglio revisore e mostra le azioni soltanto per gli articoli in attesa', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $pendingArticle = createPendingArticleForRevisionTest($author, 'Articolo da controllare');
    $acceptedArticle = createPendingArticleForRevisionTest($author, 'Articolo controllato');

    $acceptedArticle->setAccepted(true);

    $this->actingAs($revisor)
        ->get(route('revisor.article.show', $pendingArticle))
        ->assertSuccessful()
        ->assertSee('Dettaglio revisione')
        ->assertSee('id="mainCarousel"', false)
        ->assertSee('presto-revisor-main', false)
        ->assertSee('presto-nav-link-active', false)
        ->assertSee('aria-current="page"', false)
        ->assertSee(
            'class="presto-back-link presto-revisor-catalog-link d-inline-flex align-items-center gap-2"',
            false
        )
        ->assertSee('Torna al catalogo')
        ->assertDontSee('presto-revisor-back-link', false)
        ->assertSee($pendingArticle->title)
        ->assertSee('aria-label="Accetta annuncio"', false)
        ->assertSee('aria-label="Rifiuta annuncio"', false)
        ->assertDontSee('Modifica revisione');

    $this->actingAs($revisor)
        ->get(route('revisor.article.show', $acceptedArticle))
        ->assertSuccessful()
        ->assertSee($acceptedArticle->title)
        ->assertSee('Questo articolo è già stato accettato.')
        ->assertSee('Modifica revisione')
        ->assertSee(route('revisor.article.reopen', $acceptedArticle), false)
        ->assertDontSee('aria-label="Accetta annuncio"', false)
        ->assertDontSee('aria-label="Rifiuta annuncio"', false);
});

it('riapre un articolo già revisionato e permette di decidere nuovamente', function (
    string $initialDecisionRoute,
    string $newDecisionRoute,
    bool $expectedFinalStatus
) {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo da revisionare di nuovo');

    $this->actingAs($revisor)
        ->patch(route($initialDecisionRoute, $article))
        ->assertRedirect();

    $initialRevisionAction = RevisionAction::query()->firstOrFail();

    $this->actingAs($revisor)
        ->patch(route('revisor.article.reopen', $article))
        ->assertRedirect(route('revisor.article.show', $article))
        ->assertSessionHas('message', "L'articolo $article->title è tornato in revisione.");

    expect($article->fresh()->is_accepted)->toBeNull()
        ->and($initialRevisionAction->fresh()->undone_at)->not->toBeNull();

    $this->actingAs($revisor)
        ->get(route('revisor.article.show', $article))
        ->assertSuccessful()
        ->assertSee('Da revisionare')
        ->assertSee('aria-label="Accetta annuncio"', false)
        ->assertSee('aria-label="Rifiuta annuncio"', false)
        ->assertDontSee('Modifica revisione');

    $this->actingAs($revisor)
        ->patch(route($newDecisionRoute, $article))
        ->assertRedirect();

    expect($article->fresh()->is_accepted)->toBe($expectedFinalStatus)
        ->and(RevisionAction::query()->count())->toBe(2)
        ->and(RevisionAction::query()->latest('id')->firstOrFail()->new_status)->toBe($expectedFinalStatus);
})->with([
    'da accettato a rifiutato' => ['accept', 'reject', false],
    'da rifiutato ad accettato' => ['reject', 'accept', true],
]);

it('registra il revisore e gli permette di annullare la propria ultima operazione', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo da accettare');

    $this->actingAs($revisor)
        ->patch(route('accept', $article))
        ->assertRedirect();

    $revisionAction = RevisionAction::query()->firstOrFail();

    expect($article->fresh()->is_accepted)->toBeTrue()
        ->and($revisionAction->revisor_id)->toBe($revisor->getKey())
        ->and($revisionAction->previous_status)->toBeNull()
        ->and($revisionAction->new_status)->toBeTrue();

    $this->actingAs($revisor)
        ->patch(route('revisor.undo', $revisionAction))
        ->assertRedirect()
        ->assertSessionHas('message', 'La tua ultima azione è stata annullata.');

    expect($article->fresh()->is_accepted)->toBeNull()
        ->and($revisionAction->fresh()->undone_at)->not->toBeNull();
});

it('impedisce a un secondo revisore di modificare un articolo già revisionato', function () {
    $firstRevisor = User::factory()->create(['is_revisor' => true]);
    $secondRevisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo condiviso');

    $this->actingAs($firstRevisor)
        ->patch(route('accept', $article))
        ->assertRedirect();

    $this->actingAs($secondRevisor)
        ->patch(route('reject', $article))
        ->assertRedirect()
        ->assertSessionHas('message', 'Questo articolo è già stato revisionato da un altro revisore.');

    expect($article->fresh()->is_accepted)->toBeTrue()
        ->and(RevisionAction::query()->count())->toBe(1)
        ->and(RevisionAction::query()->firstOrFail()->revisor_id)->toBe($firstRevisor->getKey());
});

it('impedisce a un revisore di annullare l’operazione di un altro revisore', function () {
    $firstRevisor = User::factory()->create(['is_revisor' => true]);
    $secondRevisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo protetto');

    $this->actingAs($firstRevisor)
        ->patch(route('reject', $article))
        ->assertRedirect();

    $revisionAction = RevisionAction::query()->firstOrFail();

    $this->actingAs($secondRevisor)
        ->patch(route('revisor.undo', $revisionAction))
        ->assertRedirect()
        ->assertSessionHas('message', 'Non puoi annullare questa revisione perché non è più la tua ultima operazione valida.');

    expect($article->fresh()->is_accepted)->toBeFalse()
        ->and($revisionAction->fresh()->undone_at)->toBeNull();
});

it('mostra soltanto il risultato nell’avviso della revisione appena effettuata', function (
    string $revisionRoute,
    string $expectedMessage
) {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $article = createPendingArticleForRevisionTest($author, 'Articolo con ritorno al catalogo');
    createPendingArticleForRevisionTest($author, 'Altro articolo da revisionare');

    $this->actingAs($revisor)
        ->from(route('revisor.article.show', $article))
        ->followingRedirects()
        ->patch(route($revisionRoute, $article))
        ->assertSuccessful()
        ->assertSee($expectedMessage)
        ->assertDontSee('presto-revisor-feedback-catalog', false)
        ->assertDontSee('Torna al catalogo articoli')
        ->assertDontSee('Annulla ultima azione');

    $this->actingAs($revisor)
        ->get(route('revisor.article.show', $article))
        ->assertSuccessful()
        ->assertDontSee('presto-revisor-feedback-catalog', false);
})->with([
    'accettazione' => ['accept', "Hai accettato l'articolo Articolo con ritorno al catalogo"],
    'rifiuto' => ['reject', "Hai rifiutato l'articolo Articolo con ritorno al catalogo"],
]);

it('permette di annullare soltanto l’ultima operazione del revisore', function () {
    $revisor = User::factory()->create(['is_revisor' => true]);
    $author = User::factory()->create();
    $firstArticle = createPendingArticleForRevisionTest($author, 'Primo articolo');
    $secondArticle = createPendingArticleForRevisionTest($author, 'Secondo articolo');

    $this->actingAs($revisor)->patch(route('accept', $firstArticle))->assertRedirect();
    $firstRevisionAction = RevisionAction::query()->latest('id')->firstOrFail();

    $this->actingAs($revisor)->patch(route('reject', $secondArticle))->assertRedirect();
    $secondRevisionAction = RevisionAction::query()->latest('id')->firstOrFail();

    $this->actingAs($revisor)
        ->patch(route('revisor.undo', $firstRevisionAction))
        ->assertRedirect();

    expect($firstArticle->fresh()->is_accepted)->toBeTrue()
        ->and($secondArticle->fresh()->is_accepted)->toBeFalse()
        ->and($firstRevisionAction->fresh()->undone_at)->toBeNull();

    $this->actingAs($revisor)
        ->patch(route('revisor.undo', $secondRevisionAction))
        ->assertRedirect();

    expect($secondArticle->fresh()->is_accepted)->toBeNull()
        ->and($secondRevisionAction->fresh()->undone_at)->not->toBeNull();
});
