<div>
    <div class="presto-detail-media rounded-4 overflow-hidden position-relative">
        @if ($slides !== [])
        <div wire:key="carousel-slide-{{ $article->id }}-{{ $currentSlide }}" class="presto-carousel-frame h-100">
            <img src="{{ $slides[$currentSlide]['src'] }}" class="presto-carousel-image d-block w-100 h-100 object-fit-contain" alt="{{ $slides[$currentSlide]['alt'] }}">
        </div>

        <button type="button" class="btn presto-btn-light presto-carousel-control presto-carousel-control-prev rounded-circle" wire:click="prevSlide" aria-label="{{ __('ui.previous_image') }}">
            &lt;
        </button>

        <button type="button" class="btn presto-btn-light presto-carousel-control presto-carousel-control-next rounded-circle" wire:click="nextSlide" aria-label="{{ __('ui.next_image') }}">
            &gt;
        </button>

        <div class="presto-carousel-dots position-absolute bottom-0 start-50 translate-middle-x mb-3 d-flex gap-2 px-3 py-2 rounded-pill">
            @foreach ($slides as $index => $slide)
            <button
                type="button"
                wire:key="carousel-dot-{{ $article->id }}-{{ $index }}"
                wire:click="goToSlide({{ $index }})"
                class="presto-carousel-dot {{ $currentSlide === $index ? 'active' : '' }}"
                aria-label="{{ __('ui.go_to_image', ['number' => $index + 1]) }}"></button>
            @endforeach
        </div>
        @else
        <div class="h-100 d-flex align-items-center justify-content-center bg-body-tertiary">
            <p class="mb-0">{{ __('ui.no_image_available') }}</p>
        </div>
        @endif
    </div>
</div>
