<x-layout>
    <section class="presto-revisor-page">
        <div class="container-fluid presto-revisor-container">
            <header class="presto-revisor-header text-center">
                <div class="presto-back-link-wrapper mb-4">
                    <a href="{{ route('revisor_index') }}"
                        class="presto-back-link presto-revisor-catalog-link d-inline-flex align-items-center gap-2">
                        <x-presto-arrow-right-circle
                            class="presto-icon presto-icon-arrow presto-arrow-back"
                            aria-hidden="true" focusable="false" />
                        {{ __('ui.back_to_catalog') }}
                    </a>
                </div>
                <span class="presto-eyebrow text-uppercase fw-bold">{{ __('ui.moderation_area') }}</span>
                <h1 class="mb-2">{{ __('ui.review_detail') }}</h1>
            </header>

            @if (session()->has('message'))
            <div @class([ 'presto-revisor-feedback' , 'presto-alert-success'=> session('messageType', 'success') === 'success',
                'presto-alert-danger' => session('messageType') === 'danger',
                ]) role="status">
                <span>{{ session('message') }}</span>
            </div>
            @endif

            <article class="presto-revisor-card">
                <div class="presto-revisor-gallery">
                    @if ($article->images->isNotEmpty())
                        <div id="revisorCarousel" class="carousel slide" data-bs-ride="false">
                            <div class="carousel-inner rounded overflow-hidden shadow-sm">
                                @foreach ($article->images as $image)
                                    <div class="carousel-item @if($loop->first) active @endif">
                                        <div class="d-flex flex-column align-items-center w-100 position-relative">
                                            <figure class="presto-revisor-image mb-0 w-100">
                                                <img
                                                    class="presto-revisor-photo w-100 object-fit-cover"
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($image->path).'?v='.$image->updated_at->getTimestamp() }}"
                                                    alt="{{ __('ui.image_alt_index', ['index' => $loop->iteration, 'title' => $article->title]) }}">
                                                <img
                                                    class="presto-revisor-watermark"
                                                    src="{{ asset('media/Logo2.png') }}"
                                                    alt=""
                                                    aria-hidden="true">
                                            </figure>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($article->images->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#revisorCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon p-3 bg-dark bg-opacity-50 rounded-circle" aria-hidden="true"></span>
                                    <span class="visually-hidden">Precedente</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#revisorCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon p-3 bg-dark bg-opacity-50 rounded-circle" aria-hidden="true"></span>
                                    <span class="visually-hidden">Successivo</span>
                                </button>

                                <div class="carousel-indicators position-relative mt-2 mb-0">
                                    @foreach ($article->images as $image)
                                        <button type="button" data-bs-target="#revisorCarousel" data-bs-slide-to="{{ $loop->index }}" 
                                            class="@if($loop->first) active @endif bg-dark" 
                                            aria-current="@if($loop->first) true @endif" 
                                            aria-label="Slide {{ $loop->iteration }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @else
                        <figure class="presto-revisor-image mb-0">
                            <img
                                class="presto-revisor-photo"
                                src="https://picsum.photos/seed/presto-review-{{ $article->id }}/900/700"
                                alt="{{ __('ui.no_image_alt', ['title' => $article->title]) }}">
                        </figure>
                    @endif
                </div>

                <div class="presto-revisor-content">
                    <div>
                        @if (is_null($article->is_accepted))
                        <span class="presto-revisor-status presto-revisor-status-pending">{{ __('ui.status_pending') }}</span>
                        @elseif ($article->is_accepted)
                        <span class="presto-revisor-status presto-revisor-status-accepted">{{ __('ui.status_accepted') }}</span>
                        @else
                        <span class="presto-revisor-status presto-revisor-status-rejected">{{ __('ui.status_rejected') }}</span>
                        @endif

                        <h2>{{ $article->title }}</h2>

                        <div class="presto-revisor-meta">
                            <span>{{ __('ui.author') }}: <strong>{{ $article->user?->name ?? __('ui.unknown_user') }}</strong></span>
                            <span class="presto-revisor-category">
                                #{{ $article->category ? __("ui.{$article->category->name}") : __('ui.uncategorized') }}
                            </span>
                        </div>

                        <p class="presto-revisor-price">
                            &euro; {{ number_format((float) $article->price, 2, ',', '.') }}
                        </p>

                        <div class="presto-revisor-description">
                            <h3>{{ __('ui.description') }}</h3>
                            <p class="mb-0">{{ $article->description }}</p>
                        </div>
                    </div>

                    @if (is_null($article->is_accepted))
                    <div class="presto-revisor-actions">
                        <form action="{{ route('accept', $article) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <span class="presto-revisor-action-label">{{ __('ui.accept') }}</span>
                            <button class="btn presto-revisor-action presto-revisor-accept" type="submit"
                                aria-label="{{ __('ui.accept_article') }}" title="{{ __('ui.accept_article') }}">
                                <x-presto-check-circle-fill class="presto-revisor-action-icon" aria-hidden="true" />
                            </button>
                        </form>

                        <form action="{{ route('reject', $article) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <span class="presto-revisor-action-label">{{ __('ui.reject') }}</span>
                            <button class="btn presto-revisor-action presto-revisor-reject" type="submit"
                                aria-label="{{ __('ui.reject_article') }}" title="{{ __('ui.reject_article') }}">
                                <x-presto-x-circle-fill class="presto-revisor-action-icon" aria-hidden="true" />
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="presto-revisor-reviewed-actions">
                        <div @class([ 'presto-revisor-reviewed-note' , 'presto-revisor-reviewed-note-accepted'=> $article->is_accepted,
                            'presto-revisor-reviewed-note-rejected' => ! $article->is_accepted,
                            ])>
                            @if ($article->is_accepted)
                            <x-presto-check-circle-fill aria-hidden="true" focusable="false" />
                            <span>{{ __('ui.article_already_accepted') }}</span>
                            @else
                            <x-presto-x-circle-fill aria-hidden="true" focusable="false" />
                            <span>{{ __('ui.article_already_rejected') }}</span>
                            @endif
                        </div>

                        <form action="{{ route('revisor.article.reopen', $article) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn presto-revisor-action presto-revisor-reopen" type="submit"
                                aria-label="{{ __('ui.modify_review_for', ['title' => $article->title]) }}">
                                <x-ionicon-reload-sharp class="presto-revisor-action-icon" aria-hidden="true" />
                                <span>{{ __('ui.modify_review') }}</span>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </article>
        </div>
    </section>
</x-layout>
