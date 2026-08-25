<main class="presto-auth-page container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="presto-auth-card presto-auth-card-login rounded-5 p-4 p-lg-5">
                <div class="presto-auth-header text-center mb-4">
                    <span class="presto-auth-brand">
                    <img src="{{ asset('media/Logo2.png') }}" alt="{{ __('ui.logo_alt') }}">
                    </span>
                    <h1 class="h2 mt-4 mb-2">{{ __('ui.welcome_back') }}</h1>
                    <p class="text-muted mb-0">{{ __('ui.login_subtitle') }}</p>
                </div>
                <form class="presto-auth-form" wire:submit="login">
                    <div class="presto-auth-field mb-3">
                        <label for="email" class="presto-auth-label form-label">
                            <x-presto-envelope-at-fill class="presto-auth-label-icon" />
                            {{ __('ui.email_label') }}
                        </label>
                        <input type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" id="email" placeholder="{{ __('ui.email_placeholder') }}" wire:model="email">@error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="presto-auth-field mb-3">
                        <label for="password" class="presto-auth-label form-label">
                            <x-presto-shield-lock-fill class="presto-auth-label-icon" />
                            {{ __('ui.password_label') }}
                        </label>
                        <input type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" id="password" placeholder="{{ __('ui.password_placeholder') }}" wire:model="password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit"
                        class="btn presto-btn-dark d-inline-flex align-items-center justify-content-center gap-2 rounded-pill w-100 py-3">
                        {{ __('ui.login_btn') }}
                        <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow" />
                    </button>
                    {{-- L'accesso con google è visibile solo a chi lo ha configurato , guardare il readme --}}
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
                    {{-- Link per il recupero password --}}
                    <a href="{{route('password.request')}}" class="btn presto-google-btn rounded-pill w-100 py-3 mt-3">{{ __('ui.forgot_password') }}</a>
                </form>
                <p class="presto-auth-switch text-center text-muted mt-4 mb-0">{{ __('ui.no_account') }} <a href="{{ route('register') }}" class="presto-text-link">{{ __('ui.register_link') }}</a></p>
            </div>
        </div>
    </div>
    </div>
</main>
