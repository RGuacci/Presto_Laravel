<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <h2>{{ __('ui.reset_password_title') }}</h2>

            <p>
                {{ __('ui.reset_password_instructions') }}
            </p>

            <form wire:submit="resetPassword">

                <div class="mb-3">
                    <label class="form-label">{{ __('ui.email_label') }}</label>

                    <input type="email" class="form-control" wire:model="email" readonly>
                </div>

                <div class="mb-3">

                    <label class="form-label">{{ __('ui.new_password_label') }}</label>

                    <input type="password" class="form-control" wire:model="password">

                    @error('password')
                        <small class="text-danger">
                            {{ $message }}
                        </small>
                    @enderror

                </div>

                <div class="mb-3">

                    <label class="form-label">{{ __('ui.confirm_password_label') }}</label>

                    <input type="password" class="form-control" wire:model="password_confirmation">

                </div>

                <button class="btn btn-primary">

                    {{ __('ui.update_password') }}

                </button>

            </form>

        </div>

    </div>

</div>
