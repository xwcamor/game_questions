@extends('layouts.app')
@section('title','Unirse a '.$room->title.' | PlayLab')
@section('content')
<div class="auth-wrap"><form class="form-card auth-card" method="POST" action="{{ route('rooms.join') }}">@csrf
<span class="eyebrow">TE HAN INVITADO A JUGAR</span><h1>{{ $room->title }}</h1><p class="muted">{{ $room->quiz->title }}</p>
<div class="join-code-large">{{ $room->code }}</div><input type="hidden" name="code" value="{{ $room->code }}">
<label>Tu nombre<input name="display_name" maxlength="40" value="{{ old('display_name',auth()->user()->name ?? '') }}" required placeholder="Escribe cómo te verán"></label>
<label>Elige tu avatar<select name="avatar">@foreach(['🎮','🚀','🦊','🐼','🐸','🐯','🦄','🐙','🤖','🐱','🐸','👾'] as $avatar)<option value="{{ $avatar }}">{{ $avatar }}</option>@endforeach</select></label>
@if($room->visibility==='private' && $room->password_hash)<label>Contraseña de sala<input type="password" name="password" required></label>@endif
<button class="btn btn-primary btn-block btn-lg">Unirme a la partida →</button></form></div>
@endsection
