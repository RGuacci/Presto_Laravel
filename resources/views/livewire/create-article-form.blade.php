<div class="presto-form-shell">
    @if (session('success'))
    <div class="alert presto-alert-success rounded-4 mb-4">{{ session('success') }}
    </div>
    @endif
    <form class="presto-form-card rounded-5 p-4 p-lg-5" wire:submit="save">
        <div class="presto-create-form-header text-center mb-4">
            <span class="presto-create-form-logo">
                <img src="{{ asset('media/Logo2.png') }}" alt="{{ __('ui.logo_alt') }}">
            </span>
            <h2 class="h3 mt-3 mb-1">{{ __('ui.ad_details') }}</h2>
            <p class="text-muted mb-0">{{ __('ui.present_best') }}</p>
        </div>
        <div class="mb-4">
            <label for="title" class="form-label">{{ __('ui.ad_title') }}</label>
            <input type="text" class="form-control form-control-lg @error('title') is-invalid @enderror" id="title" placeholder="{{ __('ui.title_placeholder') }}" wire:model="title">
            @error('title')<div class="invalid-feedback">{{ $message }}
            </div>
            @enderror
        </div>
        <div class="mb-4">
            <label for="description" class="form-label">{{ __('ui.description_label') }}</label>
            <textarea id="description" rows="5" class="form-control @error('description') is-invalid @enderror" placeholder="{{ __('ui.description_placeholder') }}" wire:model="description">
            </textarea>
            @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="price" class="form-label">{{ __('ui.price_label') }}</label>
                <div class="input-group input-group-lg">
                    <span class="input-group-text">€</span>
                    <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" placeholder="0,00" wire:model="price">
                </div>
                @error('price')
                <div class="invalid-feedback d-block">{{ $message }}
                </div>
                @enderror
            </div>
            <div class="col-md-6">

                <label for="category" class="form-label">{{ __('ui.category_label') }}</label>


                <select id="category" wire:model="category" class="form-select form-select-lg @error('category') is-invalid @enderror">
                    <option value="">{{ __('ui.select_category') }}</option>

                    @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ __("ui.{$category->name}") }}</option>
                    @endforeach
                </select>
                @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="mb-4">
            <label for="images" class="form-label">{{ __('ui.images_label') }}</label>
            <input
                id="images"
                type="file"
                multiple
                accept="image/*"
                class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror"
                wire:model="images">
            @error('images')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('images.*')
            <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="text-muted d-block mt-2">{{ __('ui.images_help') }}</small>

            <div wire:loading wire:target="images" class="small text-muted mt-2">{{ __('ui.uploading_images') }}</div>

            @if ($images !== [])
            <div class="row g-3 mt-2">
                @foreach ($images as $index => $image)
                <div class="col-6 col-md-4" wire:key="preview-image-{{ $index }}">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="{{ $image->temporaryUrl() }}" class="card-img-top" alt="{{ __('ui.preview_image_alt', ['number' => $index + 1]) }}">
                        <div class="card-body p-2">
                            <button type="button" class="btn btn-sm btn-outline-danger w-100" wire:click="removeImage({{ $index }})">
                                {{ __('ui.remove_image') }}
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="presto-form-tip rounded-4 p-3 mb-4">
            <x-presto-sparkles class="presto-icon presto-icon-xs me-2" /> {{ __('ui.clear_ad_tip') }}
        </div>
        <button type="submit" class="btn presto-btn-red presto-interactive rounded-pill w-100 py-3 fw-bold">{{ __('ui.publish_ad_btn') }} <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow ms-2" /></button>
    </form>
</div>
