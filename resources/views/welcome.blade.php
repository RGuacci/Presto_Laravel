<x-layout>

    <section class="presto-home-listings">
        <div class="presto-home-listings-background">
            <div id="homeListingsCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel">
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

        <div class="container py-5 position-relative">
            @if (session()->has('errorMessage'))
            <div class="alert alert-danger text-center shadow rounded w-50">
                {{ session('errorMessage') }}
            </div>
            @endif
            @if (session()->has('errorRequest'))
            <div class="alert alert-danger text-center shadow rounded w-50 mx-auto mb-4">
                {{ session('errorRequest') }}
            </div>
            @endif
            <div class="presto-listing-toolbar mb-4">
                <span class="presto-results-count">
                    <x-presto-collection-fill class="presto-icon presto-results-icon" aria-hidden="true"
                        focusable="false" />
                    {{ __('ui.recent_ads_tag') }}
                </span>
                <h2 class="presto-listing-title h2 mb-0">{{ __('ui.latest_ads_title') }}</h2>
                <div class="presto-category-menu">
                    <a href="{{ route('article.index') }}" class="presto-home-view-all">
                        {{ __('ui.view_all') }}
                        <x-presto-arrow-right-circle class="presto-icon" />
                    </a>
                </div>
            </div>
            <div class="row g-4">
                @forelse ($articles as $article)
                <div class="col-12 col-md-6 col-xl-4"><x-card :article="$article" /></div>
                @empty
                <div class="col-12">
                    <div class="presto-empty-state text-center rounded-4 p-5"><x-presto-search
                            class="presto-icon presto-icon-cta mb-3" />
                        <h3>{{ __('ui.no_ads_yet') }}</h3>
                        <p class="mb-0">{{ __('ui.be_the_first') }}</p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="presto-trust-section py-5">
        <div class="container">
            <div class="presto-timeline" aria-label="{{ __('ui.how_presto_works_aria') }}">
                <div class="presto-timeline-track" aria-hidden="true">
                    <span class="presto-timeline-progress"></span>
                </div>

                <article class="presto-timeline-step">
                    <span class="presto-trust-icon" data-step="1" aria-hidden="true"></span>
                    <div class="presto-timeline-copy">
                        <h3 class="h4 mt-3"> {{__('ui.search')}}</h3>
                        <p class="mb-0"> {{__('ui.search_description')}}</p>
                    </div>
                </article>

                <article class="presto-timeline-step">
                    <span class="presto-trust-icon" data-step="2" aria-hidden="true"></span>
                    <div class="presto-timeline-copy">
                        <h3 class="h4 mt-3"> {{__('ui.contact')}}</h3>
                        <p class="mb-0"> {{__('ui.contact_description')}}</p>
                    </div>
                </article>

                <article class="presto-timeline-step">
                    <span class="presto-trust-icon" data-step="3" aria-hidden="true"></span>
                    <div class="presto-timeline-copy">
                        <h3 class="h4 mt-3"> {{__('ui.complete')}}</h3>
                        <p class="mb-0"> {{__('ui.complete_description')}}</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</x-layout>
