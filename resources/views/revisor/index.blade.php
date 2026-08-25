<x-layout>
    <section class="presto-revisor-page">
        <div class="container-fluid presto-revisor-container">
            <header class="presto-revisor-header text-center">
                <span class="presto-eyebrow text-uppercase fw-bold">{{ __('ui.moderation_area') }}</span>
                <h1 class="mb-2">{{ __('ui.revision_catalog') }}</h1>
                <p>{{ __('ui.revision_catalog_subtitle') }}</p>
            </header>

            <div class="presto-revisor-catalog-summary" id="revisorCatalogSummary">
                <x-presto-collection-fill aria-hidden="true" focusable="false" />
                <span>
                    {{ trans_choice('ui.articles_in_catalog', $articles->total(), ['count' => $articles->total()]) }}
                </span>
            </div>

            @if (session()->has('message'))
            <div class="alert alert-{{ session('messageType', 'success') }} presto-revisor-feedback"
                role="status">
                {{ session('message') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert alert-danger presto-revisor-feedback" role="alert">
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('revisor.articles.review_selected') }}"
                class="presto-revisor-selection-form" data-review-selection-form>
                @csrf
                @method('PATCH')

                @if ($hasPendingArticles)
                <div class="presto-revisor-selection-toolbar">
                    <p class="presto-revisor-selection-count mb-0" aria-live="polite">
                        <strong data-review-selection-count>0</strong>
                        <span>{{ __('ui.selected_articles') }}</span>
                    </p>

                    <div class="presto-revisor-selection-actions">
                        <button type="submit" name="decision" value="reject"
                            class="presto-revisor-selection-action presto-revisor-selection-reject"
                            data-review-action>
                            <x-presto-x-circle-fill aria-hidden="true" focusable="false" />
                            {{ __('ui.reject_selected') }}
                        </button>
                        <button type="submit" name="decision" value="accept"
                            class="presto-revisor-selection-action presto-revisor-selection-accept"
                            data-review-action>
                            <x-presto-check-verified-02 aria-hidden="true" focusable="false" />
                            {{ __('ui.accept_selected') }}
                        </button>
                    </div>
                </div>
                @endif

                <div class="presto-revisor-table-wrapper" role="region" aria-label="{{ __('ui.revision_catalog_region') }}"
                    aria-describedby="revisorCatalogSummary" tabindex="0">
                    <table class="presto-revisor-table">
                        <caption class="visually-hidden">
                            {{ __('ui.revision_table_caption') }}
                        </caption>
                        <thead>
                            <tr>
                                <th scope="col" class="presto-revisor-table-selection-heading">
                                    @if ($hasPendingArticles)
                                    <input type="checkbox" class="presto-revisor-table-checkbox"
                                        data-review-select-all aria-label="{{ __('ui.select_all_to_review') }}">
                                    @else
                                    <span class="visually-hidden">{{ __('ui.selection') }}</span>
                                    @endif
                                </th>
                                <th scope="col">{{ __('ui.th_article') }}</th>
                                <th scope="col">{{ __('ui.th_category') }}</th>
                                <th scope="col">{{ __('ui.th_price') }}</th>
                                <th scope="col">{{ __('ui.th_status') }}</th>
                                <th scope="col">{{ __('ui.th_published_at') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($articles as $article)
                            <x-revisor-table-row :$article />
                            @empty
                            <tr>
                                <td colspan="6" class="presto-revisor-table-empty-cell">
                                    <div class="presto-revisor-empty presto-revisor-catalog-empty text-center">
                                        <span class="presto-revisor-empty-title text-uppercase fw-bold">
                                            <x-presto-check-verified-02 class="presto-revisor-empty-title-icon"
                                                aria-hidden="true" focusable="false" />
                                            <span>{{ __('ui.all_checked') }}</span>
                                        </span>
                                        <h2>{{ __('ui.no_published_articles') }}</h2>
                                        <a href="{{ route('article.index') }}"
                                            class="btn presto-revisor-home-link">
                                            {{ __('ui.go_to_all_articles') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>

            @if ($articles->hasPages())
            <div class="presto-pagination presto-revisor-pagination">
                {{ $articles->links() }}
            </div>
            @endif
        </div>
    </section>
</x-layout>
