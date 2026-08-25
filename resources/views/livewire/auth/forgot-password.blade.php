<section class="presto-auth-page presto-forgot-page container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="presto-auth-card presto-auth-card-forgot rounded-5 p-4 p-lg-5">
                @if (session()->has('success'))
                    <div class="presto-forgot-success alert alert-success mb-4" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="presto-auth-header text-center mb-4">
                    <span class="presto-auth-brand">
                        <img src="{{ asset('media/Logo2.png') }}" alt="{{ __('ui.logo_alt') }}">
                    </span>
                    <p class="presto-forgot-eyebrow text-uppercase fw-bold mt-4 mb-2">{{ __('ui.recover_account') }}</p>
                    <h1 class="mb-3"> {{ __('ui.forgot_password') }}</h1>
                    <p class="mb-0">
                         {{ __('ui.forgot_password_description') }}
                    </p>
                </div>

                <form class="presto-auth-form" wire:submit="sendResetLink">
                    <div class="presto-auth-field mb-4">
                        <label for="forgot-email" class="presto-auth-label form-label">
                            <x-presto-envelope-at-fill class="presto-auth-label-icon" />
                           {{ __('ui.email_address') }}
                        </label>
                        <input
                            type="email"
                            class="form-control form-control-lg @error('email') is-invalid @enderror"
                            id="forgot-email"
                            placeholder="{{ __('ui.email_placeholder') }}"
                            wire:model="email"
                        >
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn presto-auth-submit presto-btn-dark w-100 py-3">
                        {{ __('ui.send_reset_link') }}
                        <x-presto-arrow-right-circle class="presto-icon presto-icon-arrow ms-2" />
                    </button>
                </form>

                <p class="presto-auth-switch text-center mt-4 mb-0">
                   {{ __('ui.remember_password') }}
                    <a href="{{ route('login') }}" class="presto-text-link">{{ __('ui.back_to_login') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>
