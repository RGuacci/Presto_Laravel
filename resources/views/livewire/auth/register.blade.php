<main class="presto-auth-page container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-6">
            <div class="presto-auth-card presto-auth-card-register rounded-5 p-4 p-lg-5">
                <div class="presto-auth-header text-center mb-4"><span class="presto-auth-brand">
                        <img src="{{ asset('media/Logo2.png') }}" alt="{{ __('ui.logo_alt') }}">
                    </span>
                    <h1 class="h2 mt-4 mb-2">{{ __('ui.join_community') }}</h1>
                    <p class="text-muted mb-0">{{ __('ui.register_subtitle') }}</p>
                </div>
                <form class="presto-auth-form" wire:submit="register">
                    <div class="presto-auth-field mb-3">
                        <label for="name" class="presto-auth-label form-label">
                            <x-presto-person-vcard-fill class="presto-auth-label-icon" />
                            {{ __('ui.name_label') }}
                        </label>
                        <input type="text" class="form-control form-control-lg @error('name') is-invalid @enderror" id="name" placeholder="{{ __('ui.name_placeholder') }}" wire:model="name">@error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="presto-auth-field mb-3">
                        <label for="email" class="presto-auth-label form-label">
                            <x-presto-envelope-at-fill class="presto-auth-label-icon" />
                            {{ __('ui.email_label') }}
                        </label>
                            <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" placeholder="{{ __('ui.email_placeholder') }}" wire:model="email">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="presto-auth-label form-label">
                                <x-presto-shield-lock-fill class="presto-auth-label-icon" />
                                {{ __('ui.password_label') }}
                            </label>
                            <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" placeholder="{{ __('ui.password_min_placeholder') }}" wire:model="password">@error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="presto-auth-label form-label">
                                <x-presto-shield-lock-fill class="presto-auth-label-icon" />
                                {{ __('ui.confirm_password_label') }}
                            </label>
                            <input type="password" class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" id="password_confirmation" placeholder="{{ __('ui.confirm_password_placeholder') }}" wire:model="password_confirmation">
                            @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <p class="presto-auth-security mb-4">
                        <x-presto-shield-lock-fill class="presto-auth-security-icon" />
                        {{ __('ui.data_protected') }}
                    </p>
                    <button type="submit"
                        class="btn presto-auth-submit presto-btn-red d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100 py-3">
                        {{ __('ui.register_btn') }}
                        <x-presto-person-add class="presto-icon presto-icon-arrow" />
                    </button>
                    @if(config('services.google.client_id'))
                    <div class="presto-auth-separator">
                        <span>{{ __('ui.or') }}</span>
                    </div>
                    <a href="{{ route('google.login') }}"
                        class="btn presto-google-btn d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100 py-3">
                        <x-presto-google class="presto-icon presto-icon-arrow" />
                        {{ __('ui.continue_with_google') }}
                    </a>
                    @endif
                </form>
                <p class="presto-auth-switch text-center text-muted mt-4 mb-0">{{ __('ui.already_have_account') }} <a href="{{ route('login') }}" class="presto-text-link">{{ __('ui.login_link') }}</a>
                </p>
            </div>
        </div>
    </div>
</main>
