<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Validate;
// Classi per il recupero password
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPassword extends Component
{  
   #[Validate('required|email')]
     public string $email = '';

     public function sendResetLink()
     {
        $this->validate();
        $status = Password::sendResetLink([
          'email'=>$this->email
        ]);
        // Laravel cerca l'utente ed invia una mail
       if($status === Password::RESET_LINK_SENT){
         session()->flash('success', __($status));
         return;
        //  Se non dovesse trovare la mail nel database si vedrebbe l'errore sotto il form di inserimento della mail
       }else{
         throw ValidationException::withMessages([
         'email' => [__($status)],
         ]);
       }
        
     }
  
    public function render()
    {
        return view('livewire.auth.forgot-password')->layout('components.layout');
    }
}
