@extends('layouts.app')
@section('title','Nuevo cuestionario | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">NUEVO CONTENIDO</span><h1>Crear cuestionario</h1><p class="muted">Dale un nombre y configura la partida.</p></div></div>
<form class="form-card form-wide" method="POST" action="{{ route('quizzes.store') }}">@csrf
<label>Nombre del cuestionario<input name="title" maxlength="160" value="{{ old('title') }}" required placeholder="Ej. Fundamentos de redes"></label>
<label>Descripción<textarea name="description" rows="3" maxlength="2000" placeholder="¿De qué trata este cuestionario?">{{ old('description') }}</textarea></label>
<label>Tiempo predeterminado por pregunta<select name="time_limit_seconds">@foreach([5,10,15,20,30,60,120] as $s)<option value="{{ $s }}" @selected(old('time_limit_seconds',20)==$s)>{{ $s }} segundos</option>@endforeach</select></label>
<div class="check-grid"><label class="check-card"><input type="checkbox" name="randomize_questions" value="1" checked><span><strong>Mezclar preguntas</strong><small>Cada sala recibe un orden aleatorio.</small></span></label><label class="check-card"><input type="checkbox" name="randomize_answers" value="1" checked><span><strong>Mezclar alternativas</strong><small>Reduce pistas por posición.</small></span></label></div>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('quizzes.index') }}">Cancelar</a><button class="btn btn-primary">Crear y agregar preguntas →</button></div></form>
@endsection
