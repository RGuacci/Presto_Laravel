<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{   
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
      
    public function login()
    {     
        $this->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
        ]);
          
      if (! Auth::attempt([ 'email' => $this->email,'password' => $this->password,], $this->remember)) {  //qui internamente laravel riceve un array e fa un controllo ,restituisce un booleano

        $this->addError('email', __('auth.failed')); //Laravel associa l'errore di autenticazione al campo email

    }

      session()->regenerate();  //Questa parte è fondamentale , rigenera la sessione per evitare attacchi esterni su sessioni vecchie

      return redirect()->route('create.article');  
    } 

    public function render()
    {
        return view('livewire.auth.login');
    }
}
