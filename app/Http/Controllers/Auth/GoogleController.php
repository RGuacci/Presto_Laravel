<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{  
   public function redirect()
  {
    return Socialite::driver('google')->redirect();
  }
  
 
   public function callback()
{
    // Recupero i dati dell'utente da Google
    $googleUser = Socialite::driver('google')->user();

    // Cerco un utente con la stessa email
    $user = User::where('email', $googleUser->email)->first();

    if ($user) {

        // Collego l'account Google all'utente esistente
        $user->update([
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
        ]);

    } else {

        // Creo un nuovo utente tramite Google
        $user = User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
            'password' => Hash::make(Str::random(40)),
        ]);

    }

    // Autentico l'utente
   Auth::login($user, true);  //Se l'utente ha gia loggato rimane salvato

    // Reindirizzo alla homepage
    return redirect()->route('homepage');
}

 

}
