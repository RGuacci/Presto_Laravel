<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {  
       //Logica per fare in modo che laravel abbia sempre una lingua da selezionare avviando il sito 
      if(!session()->has('locale')){
        session()->put('locale',config('app.locale'));
      }
       
       App::setLocale(session('locale', config('app.locale')));

       return $next($request);

    }
}
