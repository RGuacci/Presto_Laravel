<?php

use App\Models\User;

beforeEach(function () {
    app()->setLocale('it');
});

it('mostra agli ospiti la candidatura con il popup di accesso e registrazione', function () {
    $this->view('components.footer')
        ->assertSee('presto-revisor-request', false)
        ->assertSee('data-bs-target="#guestRevisorModal"', false)
        ->assertSee('Accedi o registrati')
        ->assertSee('Per inviare la tua candidatura come revisore devi prima registrarti')
        ->assertSee('href="'.route('login').'"', false)
        ->assertSee('href="'.route('register').'"', false)
        ->assertDontSee(route('become.revisor'), false);
});

it('mostra agli utenti autenticati non revisori il link per inviare la candidatura', function () {
    $user = new User;
    $user->forceFill([
        'name' => 'Utente autenticato',
        'email' => 'utente@example.com',
        'is_revisor' => false,
    ]);

    $this->actingAs($user)
        ->view('components.footer')
        ->assertSee('presto-revisor-request', false)
        ->assertSee('href="'.route('become.revisor').'"', false)
        ->assertDontSee('guestRevisorModal');
});

it('nasconde la candidatura agli utenti revisori', function () {
    $user = new User;
    $user->forceFill([
        'name' => 'Utente revisore',
        'email' => 'revisore@example.com',
        'is_revisor' => true,
    ]);

    $this->actingAs($user)
        ->view('components.footer')
        ->assertDontSee('presto-revisor-request', false)
        ->assertDontSee(route('become.revisor'), false)
        ->assertDontSee('guestRevisorModal');
});
