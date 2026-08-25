<div>
    {{-- Sezione Esplora & Risultati --}}
    <section class="container py-5">
        <div class="row justify-content-center mb-4">
            <div class="col-lg-5">
                {{-- Messaggio di conferma creazione/eliminazione/modifica --}}
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="presto-searchbar d-flex align-items-center gap-2 p-2 rounded-pill shadow-sm bg-white">
                <x-presto-search class="presto-icon presto-icon-nav ms-2" />
                
                {{-- Ricerca reattiva in tempo reale --}}
                <input
                wire:model.live.debounce.300ms="q"
                class="form-control border-0 shadow-none"
                type="search"
                placeholder="{{ __('ui.search_placeholder') }}"
                aria-label="{{ __('ui.search_aria') }}">
                
                {{-- Indicatore di caricamento discreto --}}
                <div wire:loading wire:target="q" class="spinner-border spinner-border-sm text-secondary me-2" role="status">
                    <span class="visually-hidden">{{ __('ui.loading') }}</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="presto-listing-toolbar mb-4">
        <span class="presto-results-count">
            <x-presto-collection-fill class="presto-icon presto-results-icon" aria-hidden="true" focusable="false" />
            {{ $articles->total() }} {{ __('ui.results') }}
        </span>
        <h2 class="presto-listing-title h2 mb-0">{{ __('ui.recent_articles') }}</h2>
        <div class="presto-category-filter">
            <details class="presto-category-menu">
                <summary>
                    {{ __("ui." . ($selectedCategoryName ?? 'Tutti')) }}
                    <x-presto-chevron-down class="presto-icon" aria-hidden="true" focusable="false" />
                </summary>
                <div class="presto-category-menu-panel">
                    <button
                    type="button"
                    wire:click="selectCategory(0)"
                    class="presto-category-menu-option {{ $selectedCategory === 0 ? 'active' : '' }}">
                    {{ __('ui.Tutti') }}
                </button>
                @foreach ($categories as $category)
                <button
                type="button"
                wire:key="category-filter-{{ $category->id }}"
                wire:click="selectCategory({{ $category->id }})"
                class="presto-category-menu-option {{ $selectedCategory === $category->id ? 'active' : '' }}">
                {{ __("ui.{$category->name}") }}
            </button>
            @endforeach
        </div>
    </details>
    
    @if ($selectedCategoryName !== null)
    <button type="button"
    wire:click="selectCategory(0)"
    class="presto-category-clear"
    aria-label="{{ __('ui.remove_filter_aria', ['category' => __("ui.{$selectedCategoryName}")]) }}"
    title="{{ __('ui.clear_filter') }}">
    <x-presto-x-circle-fill class="presto-icon" aria-hidden="true" focusable="false" />
</button>
@endif
</div>
</div>

{{-- Griglia Articoli --}}
<div class="row g-4" wire:loading.class="opacity-50">
    @forelse ($articles as $article)
    <div class="col-12 col-md-6 col-xl-4" wire:key="article-{{ $article->id }}">
        <x-card :article="$article" />
    </div>
    @empty
    <div class="col-12">
        <div class="presto-empty-state text-center rounded-4 p-5">
            <x-presto-search class="presto-icon presto-icon-cta mb-3" />
            <h3>{{ __('ui.no_articles_found') }}</h3>
            <p class="mb-4">{{ __('ui.no_articles_desc') }}</p>
            <a href="{{ route('create.article') }}" class="btn presto-btn-red rounded-pill px-4">{{ __('ui.publish_ad') }}</a>
        </div>
    </div>
    @endforelse
</div>

{{-- Paginazione Reattiva --}}
<div class="presto-pagination d-flex justify-content-center mt-5">
    {{ $articles->links() }}
</div>
</section>
</div>