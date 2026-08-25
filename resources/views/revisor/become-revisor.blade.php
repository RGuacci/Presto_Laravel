<h1>Un utente ha richiesto di lavorare con noi.</h1>
<h2>Ecco i suoi dati:</h2>
<p>Nome : {{ $user->name }}</p>
<p>Email : {{ $user->email }}</p>
<p>Se vuoi che diventi revisore clicka qui:</p>
<a href="{{ route('make.revisor', copmpact('user')) }}">Rendi revisore</a>