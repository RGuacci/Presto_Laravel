<x-layout>
   <main class="container py-5">
      <div class="presto-back-link-wrapper mb-4">
         <a href="{{ route('article.index') }}" class="presto-back-link d-inline-flex align-items-center gap-2">
            <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow presto-arrow-back" />
            {{ __('ui.back_to_ads') }}
         </a>
      </div>

      <section class="row g-4 align-items-start">
         <article class="col-12 col-lg-7">
            <livewire:article-carousel :article="$article" />
               
           
            @if (auth()->id() === $article->user_id)
            <div class="presto-detail-edit-wrap flex-wrap gap-3">
               <a class="presto-detail-edit presto-interactive btn presto-btn-dark rounded-pill px-4" href="{{ route('article.edit', compact('article')) }}">
                  <x-presto-pencil-square class="presto-icon" aria-hidden="true" focusable="false" />
                  {{ __('ui.edit_ad') }}
               </a>
               
               <form action="{{route('article.destroy',$article)}}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="presto-detail-edit presto-interactive btn presto-btn-dark rounded-pill px-4" aria-label="{{ __('ui.delete_ad') }}">
                    <x-presto-x-circle-fill class="presto-icon" aria-hidden="true" focusable="false" />
                    {{ __('ui.delete_ad') }}
                  </button>
               </form>
            </div>
            @endif
         </article>

         <article class="col-12 col-lg-5">
            <div class="presto-detail-panel rounded-4 p-4 p-md-5">
               <p class="presto-eyebrow text-uppercase small fw-bold mb-2">{{ __('ui.ad_detail_eyebrow') }}</p>
               <h1 class="display-6 mb-3">{{ $article->title }}</h1>
               <p class="presto-detail-price mb-3">€ {{ number_format((float) $article->price, 2, ',', '.') }}</p>

               <div class="d-flex flex-wrap gap-2 mb-4">
                  <span class="presto-detail-category presto-filter-pill">
                     <x-presto-tag-fill class="presto-icon presto-detail-category-icon" aria-hidden="true" focusable="false" />
                     {{ $article->category ? __("ui.{$article->category->name}") : __('ui.uncategorized') }}
                  </span>
               </div>

               <hr class="presto-detail-divider my-4">

               <p class="presto-detail-description mb-4">{{ $article->description }}</p>

            </div>
         </article>
      </section>
   </main>
</x-layout>
