<?php

namespace App\Livewire\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
// Import reset password
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Component;

class ResetPassword extends Component
{
    public string $token = '';

    public string $email = '';

    #[Validate('required|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(string $token)
    {
        $this->token = $token;
        $this->email = request()->query('email');
    }

    public function resetPassword()
    {
        $this->validate();

        //   Laravel controlla password, email e token
        $status = Password::reset(

            [
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token' => $this->token,
            ],

            // Se tutto è corretto laravel laravel ci passa direttamente il modello user e la nuova password
            function ($user, $password) {

                // Qui laravel aggiorna la password , ;aravel consiglia il metodo forceFill
                $user->forceFill([
                    'password' => Hash::make($password),
                    // Qui rigeneriamo il token per evitare che le sessioni Ricordami precedenti restino valide dopo il cambio password
                    'remember_token' => Str::random(60),
                ])->save();

                // Laravel lancia l'evento e notifica che la password è stata cambiata
                event(new PasswordReset($user));
            }

        );

        if ($status === Password::PASSWORD_RESET) {

            session()->flash('success', __('ui.password_updated_success'));

            return redirect()->route('login');
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function render()
    {
        return view('livewire.auth.reset-password')->layout('components.layout');
    }
}
