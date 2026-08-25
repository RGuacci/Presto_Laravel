<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'uk' ? 'en-GB' : app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Presto.it</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="presto-page d-flex flex-column min-vh-100">
    <x-navbar />

    @if (session()->has('message'))
    <div @class([ 'presto-revisor-notice' , 'alert-danger'=> session('messageType') === 'danger',
        'alert-success' => session('messageType', 'success') === 'success',
        ]) role="status">
        <div class="container text-center">
            <p class="mb-0">{{ session('message') }}</p>
        </div>
    </div>
    @endif

    @if (request()->routeIs('homepage'))
    <header class="presto-home-header">
        <img src="{{ asset('media/header.png') }}" alt="{{ __('ui.hero_alt') }}">
    </header>
    @endif

    <main class="presto-main {{ request()->routeIs('homepage') ? 'presto-home-main' : '' }} {{ request()->routeIs('revisor_index', 'revisor.*') ? 'presto-revisor-main' : '' }} flex-grow-1 position-relative">

        @if (request()->routeIs('article.*', 'create.article', 'login', 'register', 'password.request', 'revisor_index', 'revisor.*'))
        <div class="presto-main-background">
            <div id="mainCarousel"
                class="carousel slide carousel-fade h-100"
                data-bs-ride="carousel">

                <div class="carousel-inner h-100">
                    <div class="carousel-item active h-100">
                        <img src="{{ asset('media/sfondo 3.png') }}" alt="{{ __('ui.background_ads_alt') }}">
                    </div>

                    <div class="carousel-item h-100">
                        <img src="{{ asset('media/sfondo.png') }}" alt="{{ __('ui.background_used_alt') }}">
                    </div>

                    <div class="carousel-item h-100">
                        <img src="{{ asset('media/sfondo 2.png') }}" alt="{{ __('ui.background_marketplace_alt') }}">
                    </div>
                </div>

            </div>
        </div>
        @endif

        <div class="presto-main-content position-relative">
            {{ $slot }}
        </div>

    </main>

    <x-footer />
</body>

</html>
