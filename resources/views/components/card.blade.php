<article class="presto-card presto-interactive h-100">
    <a href="{{ route('article.show', compact('article')) }}" class="text-decoration-none">
        @php
        $coverImage = $article->images->first();
        $coverImagePath = $coverImage?->card_path ?? $coverImage?->displayPath();
        $coverImageUrl = $coverImagePath !== null
        ? asset('storage/'.$coverImagePath).'?v='.$coverImage->updated_at->getTimestamp()
        : "https://picsum.photos/640/480?random={$article->id}";
        @endphp
        <div class="presto-card-media">
            <img src="{{ $coverImageUrl }}" alt="{{ __('ui.article_image_alt', ['title' => $article->title]) }}">
            <span class="presto-card-badge">
                {{ $article->category ? __("ui.{$article->category->name}") : __('ui.featured') }}
            </span>
            <span class="presto-card-favorite" aria-label="{{ __('ui.category_aria', ['category' => $article->category ? __("ui.{$article->category->name}") : __('ui.general')]) }}">
                @switch($article->category?->name)
                @case('Elettronica')
                <x-presto-laptop />
                @break
                @case('Abbigliamento')
                <x-presto-person-standing-dress />
                @break
                @case('Salute e Bellezza')
                <x-presto-heart-pulse />
                @break
                @case('Casa e Giardinaggio')
                <x-presto-house-heart />
                @break
                @case('Giocattoli')
                <x-presto-controller />
                @break
                @case('Sport')
                <x-presto-bicycle />
                @break
                @case('Animali Domestici')
                <x-presto-heart />
                @break
                @case('Libri e Riviste')
                <x-presto-book />
                @break
                @case('Accessori')
                <x-presto-bag />
                @break
                @case('Motori')
                <x-presto-car-front />
                @break
                @default
                <x-presto-grid />
                @endswitch
            </span>
        </div>
        <div class="presto-card-body p-4">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <h3 class="presto-card-title h5 mb-2">{{ $article->title }}</h3>
                <span class="presto-card-price">€ {{ number_format((float) $article->price, 2, ',', '.') }}</span>
            </div>
            <p class="presto-card-text mb-3">{{ $article->description }}</p>
            <div class="presto-card-meta d-flex align-items-center justify-content-between pt-3">
                <span><x-presto-map-pin class="presto-icon presto-icon-xs me-1" /> {{ __('ui.near_you') }}</span>
                <span class="presto-card-cta">{{ __('ui.discover') }} <x-presto-arrow-right-circle class="presto-icon presto-icon-xs ms-1" /></span>
            </div>
        </div>
    </a>
</article>
