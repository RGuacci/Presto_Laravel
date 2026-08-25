<?php

use Illuminate\Support\Str;

it('does not show a home button in the primary navigation', function () {
    $content = (string) $this->view('components.navbar');

    expect($content)->toContain('<ul class="navbar-nav');

    $primaryNavigation = Str::between($content, '<ul class="navbar-nav', '</ul>');

    expect($primaryNavigation)
        ->not->toContain(route('homepage'))
        ->not->toContain(__('ui.home'));
});
