@extends('layouts.app')
@section('title','Crear sala | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">PREPARA TU PARTIDA</span><h1>Crear una sala</h1><p class="muted">Elige un cuestionario y decide cómo podrán unirse los participantes.</p></div></div>
<form class="form-card form-wide" method="POST" action="{{ route('rooms.store') }}">@csrf
<label>Cuestionario<select name="quiz_id" required>@foreach($quizzes as $quiz)<option value="{{ $quiz->id }}" @selected((string)request('quiz_id')===(string)$quiz->id)>{{ $quiz->title }} ({{ $quiz->questions_count }} preguntas)</option>@endforeach</select></label>
<label>Nombre de la sala<input name="title" maxlength="120" value="{{ old('title','Partida de '.auth()->user()->name) }}" required></label>
<label>Visibilidad<select name="visibility" id="room-visibility"><option value="public" @selected(old('visibility')==='public')>Pública — aparecerá en el listado</option><option value="private" @selected(old('visibility','private')==='private')>Privada — solo con código</option></select></label>
<label id="room-password-field">Contraseña adicional (opcional)<input type="password" name="password" minlength="4" maxlength="64" placeholder="Mínimo 4 caracteres"></label>
<label>Máximo de participantes<select name="max_players">@foreach([10,20,30,50,100,150,200] as $n)<option value="{{ $n }}" @selected(old('max_players',50)==$n)>{{ $n }} participantes</option>@endforeach</select></label>
<div class="notice notice-info">Al crear la sala recibirás un código de seis caracteres para compartir. Cada participante podrá ingresar con su nombre y avatar.</div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('dashboard') }}">Cancelar</a><button class="btn btn-primary">Crear sala →</button></div>
</form>
<script>const visibility=document.getElementById('room-visibility'),pw=document.getElementById('room-password-field');function sync(){pw.style.opacity=visibility.value==='private'?'1':'.65';}visibility.addEventListener('change',sync);sync();</script>
@endsection
