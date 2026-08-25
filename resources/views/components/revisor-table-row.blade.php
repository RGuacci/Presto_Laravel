@props(['article'])

@php
// Verifica se tra le immagini dell'articolo ce n'è almeno una con segnalazione di rischio
$hasWarnings = $article->images->contains(function ($img) {
$fields = [$img->adult, $img->violence, $img->racy, $img->medical, $img->spoof];
foreach ($fields as $field) {
if ($field && (str_contains($field, 'exclamation') || str_contains($field, 'dash') || str_contains($field, 'danger') || str_contains($field, 'warning'))) {
return true;
}
}
return false;
});
@endphp

<tr class="presto-revisor-table-row" data-review-url="{{ route('revisor.article.show', $article) }}">
    <td class="presto-revisor-table-selection-cell" data-review-selection-cell>
        @if (is_null($article->is_accepted))
        <input type="checkbox" name="articles[]" value="{{ $article->id }}"
            class="presto-revisor-table-checkbox" data-review-checkbox
            aria-label="{{ __('ui.select', ['title' => $article->title]) }}">
        @else
        <span aria-hidden="true">&mdash;</span>
        <span class="visually-hidden">{{ __('ui.already_reviewed') }}</span>
        @endif
    </td>
    <td>
        <a href="{{ route('revisor.article.show', $article) }}" class="presto-revisor-table-article d-flex align-items-center gap-3 text-decoration-none"
            aria-label="{{ __('ui.open_in_review', ['title' => $article->title]) }}">
            <img src="{{ $article->images->isNotEmpty() ? \Illuminate\Support\Facades\Storage::url($article->images->first()->displayPath()).'?v='.$article->images->first()->updated_at->getTimestamp() : "https://picsum.photos/seed/presto-review-catalog-{$article->id}/240/180" }}"
                alt="{{ $article->title }}" loading="lazy">
            <div class="d-flex flex-column justify-content-center">
                {{-- Titolo + Badge sulla stessa riga --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <strong class="text-wrap">{{ $article->title }}</strong>

                    @if ($article->images->isNotEmpty())
                    @if (!$hasWarnings)
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-normal d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <i class="bi bi-shield-check"></i> Safe
                    </span>
                    @else
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill fw-normal d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                        <i class="bi bi-exclamation-triangle"></i> Da verificare
                    </span>
                    @endif
                    @endif
                </div>
                <small class="text-muted mt-1">{{ __('ui.by') }} {{ $article->user?->name ?? __('ui.unknown_user') }}</small>
            </div>
        </a>
    </td>
    <td>
        <span class="presto-revisor-table-category">
            {{ $article->category ? __("ui.{$article->category->name}") : __('ui.uncategorized') }}
        </span>
    </td>
    <td>
        <strong class="presto-revisor-table-price">
            &euro; {{ number_format((float) $article->price, 2, ',', '.') }}
        </strong>
    </td>
    <td>
        @if (is_null($article->is_accepted))
        <span class="presto-revisor-catalog-status presto-revisor-catalog-status-pending">
            {{ __('ui.status_pending') }}
        </span>
        @elseif ($article->is_accepted)
        <span class="presto-revisor-catalog-status presto-revisor-catalog-status-accepted">
            {{ __('ui.status_accepted') }}
        </span>
        @else
        <span class="presto-revisor-catalog-status presto-revisor-catalog-status-rejected">
            {{ __('ui.status_rejected') }}
        </span>
        @endif
    </td>
    <td>
        <time datetime="{{ $article->created_at?->toIso8601String() }}">
            {{ $article->created_at?->format('d/m/Y H:i') ?? __('ui.unknown_date') }}
        </time>
    </td>
</tr>