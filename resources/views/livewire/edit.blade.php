<div>
    <form class="bg-body-tertiary shadow rounded p-5 my-5" wire:submit="update">

        {{-- Sezione 1: Immagini già salvate nel database --}}
        <div class="mb-4">
            <label class="form-label fw-bold">{{ __('ui.current_images') }}</label>

            @if ($article->images->isEmpty())
            <p class="text-muted mb-0">{{ __('ui.no_images_uploaded') }}</p>
            @else
            <div class="row g-3">
                @foreach ($article->images as $image)
                <div class="col-6 col-md-4" wire:key="stored-image-{{ $image->id }}">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ asset('storage/'.$image->displayPath()).'?v='.$image->updated_at->getTimestamp() }}" class="card-img-top object-fit-cover" style="height: 150px;" alt="{{ __('ui.ad_image_alt', ['number' => $loop->iteration]) }}">
                        <div class="card-body p-2">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger w-100"
                                wire:click="removeStoredImage({{ $image->id }})"
                                wire:loading.attr="disabled"
                                wire:target="removeStoredImage({{ $image->id }})">
                                {{ __('ui.delete_image') }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Sezione 2: Nuova sezione per Caricamento Nuove Immagini --}}
        <div class="mb-4">
            <label for="temporary_images" class="form-label fw-bold">{{ __('ui.add_new_images') }}</label>
            <input
                type="file"
                id="temporary_images"
                class="form-control @error('temporary_images.*') is-invalid @enderror"
                wire:model="temporary_images"
                multiple
                accept="image/*">
            @error('temporary_images.*')
            <span class="text-danger small">{{ $message }}</span>
            @enderror

            {{-- Indicatori di caricamento --}}
            <div wire:loading wire:target="temporary_images" class="text-primary mt-2 small">
                {{ __('ui.loading_preview') }}
            </div>

            {{-- Anteprima nuove immagini selezionate --}}
            @if (!empty($temporary_images))
            <div class="mt-3">
                <p class="form-label text-muted small">{{ __('ui.new_images_to_save') }}</p>
                <div class="row g-3">
                    @foreach ($temporary_images as $key => $image)
                    <div class="col-6 col-md-4" wire:key="temp-image-{{ $key }}">
                        <div class="card border-0 shadow-sm h-100">
                            <img src="{{ $image->temporaryUrl() }}" class="card-img-top object-fit-cover" style="height: 150px;" alt="{{ __('ui.new_image_alt', ['number' => $key + 1]) }}">
                            <div class="card-body p-2">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary w-100"
                                    wire:click="removeTemporaryImage({{ $key }})">
                                    {{ __('ui.remove') }}
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sezione Campi Form --}}
        <div class="mb-3">
            <label for="title" class="form-label">{{ __('ui.title_label') }}</label>
            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" wire:model="title">
            @error('title')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">{{ __('ui.description_label') }}</label>
            <textarea id="description" cols="30" rows="10" class="form-control @error('description') is-invalid @enderror" wire:model="description"></textarea>
            @error('description')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">{{ __('ui.price_label') }}</label>
            <input type="text" class="form-control @error('price') is-invalid @enderror" id="price" wire:model="price">
            @error('price')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="category" class="form-label">{{ __('ui.category_label') }}</label>
            <select id="category" wire:model="category" class="form-control @error('category') is-invalid @enderror">
                <option label disabled>{{ __('ui.select_category') }}</option>
                @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ __("ui.{$category->name}") }}</option>
                @endforeach
            </select>
            @error('category')
            <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-flex justify-content-center">
            <button type="submit" class="btn presto-btn-dark" wire:loading.attr="disabled">
                {{ __('ui.edit_btn') }}
            </button>
        </div>
    </form>
</div>
