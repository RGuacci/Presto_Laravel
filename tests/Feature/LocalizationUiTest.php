<?php

use Illuminate\Support\ViewErrorBag;

it('mantiene le stesse chiavi valorizzate nei tre file ui', function () {
    $translations = collect(['it', 'uk', 'es'])
        ->mapWithKeys(fn (string $locale): array => [
            $locale => require lang_path("{$locale}/ui.php"),
        ]);

    $italianKeys = array_keys($translations->get('it'));
    sort($italianKeys);

    foreach ($translations as $locale => $lines) {
        $localeKeys = array_keys($lines);
        sort($localeKeys);

        expect($localeKeys)
            ->toBe($italianKeys)
            ->and($lines)->not->toContain('');
    }
});

it('mostra le pagine comuni nella lingua selezionata', function (
    string $locale,
    string $guestTitle,
    string $forgotPasswordTitle,
    string $resetPasswordTitle,
) {
    app()->setLocale($locale);
    $viewData = ['errors' => new ViewErrorBag];

    $this->view('components.footer')
        ->assertSee($guestTitle)
        ->assertDontSee('ui.guest_revisor_title');

    $this->view('livewire.auth.forgot-password', $viewData)
        ->assertSee($forgotPasswordTitle)
        ->assertDontSee('ui.forgot_password_title');

    $this->view('livewire.auth.reset-password', $viewData)
        ->assertSee($resetPasswordTitle)
        ->assertDontSee('ui.reset_password_title');
})->with([
    'italiano' => ['it', 'Accedi o registrati', 'Hai dimenticato la password?', 'Reimposta password'],
    'inglese' => ['uk', 'Log in or register', 'Forgot your password?', 'Reset password'],
    'spagnolo' => ['es', 'Inicia sesión o regístrate', '¿Olvidaste tu contraseña?', 'Restablecer contraseña'],
]);

it('traduce correttamente i conteggi dell area revisore', function (
    string $locale,
    int $count,
    string $expected,
) {
    app()->setLocale($locale);
    $translationKey = $count === 1 ? 'ui.article_in_catalog' : 'ui.articles_in_catalog';

    expect(__($translationKey, ['count' => $count]))
        ->toBe($expected);
})->with([
    'un articolo italiano' => ['it', 1, '1 articolo nel catalogo'],
    'più articoli italiani' => ['it', 3, '3 articoli nel catalogo'],
    'one English article' => ['uk', 1, '1 article in catalog'],
    'English articles' => ['uk', 3, '3 articles in catalog'],
    'un artículo español' => ['es', 1, '1 artículo en el catálogo'],
    'artículos españoles' => ['es', 3, '3 artículos en el catálogo'],
]);

it('traduce i messaggi delle azioni di revisione', function (
    string $locale,
    string $reopened,
    string $accepted,
    string $rejected,
) {
    app()->setLocale($locale);

    expect(__('ui.article_reopened', ['title' => 'Tavolo']))->toBe($reopened)
        ->and(__('ui.article_accepted_feedback', ['title' => 'Tavolo']))->toBe($accepted)
        ->and(__('ui.article_rejected_feedback', ['title' => 'Tavolo']))->toBe($rejected);
})->with([
    'italiano' => [
        'it',
        'L\'articolo Tavolo è tornato in revisione.',
        'Hai accettato l\'articolo Tavolo',
        'Hai rifiutato l\'articolo Tavolo',
    ],
    'inglese' => [
        'uk',
        'The listing Tavolo has returned to review.',
        'You accepted the listing Tavolo',
        'You rejected the listing Tavolo',
    ],
    'spagnolo' => [
        'es',
        'El anuncio Tavolo ha vuelto a revisión.',
        'Has aceptado el anuncio Tavolo',
        'Has rechazado el anuncio Tavolo',
    ],
]);
