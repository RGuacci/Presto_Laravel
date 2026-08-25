<?php

namespace App\Livewire\Auth;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Register extends Component
{   
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
     
    public function register()
    {
        $user = app(CreateNewUser::class)->create([      //app sta dicendo a laravel di instanziare un novo oggetto di classe CreateNewUser,sfruttando la classe gia esistente di fortify.
        'name' => $this->name,
        'email' => $this->email,
        'password' => $this->password,
        'password_confirmation' => $this->password_confirmation,
    ]);  
       Auth::login($user);
       return redirect()->route('create.article');  //homepage va sostituita con la pagina di creazione degli annunci
    }
    
    public function render()
    {
        return view('livewire.auth.register');
    }
}
