@extends('layouts.app')
@section('title','Panel | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">TU ESPACIO DE JUEGO</span><h1>¡Hola, {{ auth()->user()->name }}! 👋</h1><p class="muted">Prepara tu próxima partida o continúa creando contenido.</p></div><a class="btn btn-primary" href="{{ route('rooms.create') }}">＋ Crear sala</a></div>
<div class="stats-grid"><article class="stat-card"><span>Cuestionarios</span><strong>{{ $quizzes }}</strong><small>En tu biblioteca</small></article><article class="stat-card"><span>Salas creadas</span><strong>{{ $rooms }}</strong><small>Historial de partidas</small></article><article class="stat-card"><span>Salas activas</span><strong>{{ $activeRooms }}</strong><small>Esperando o jugando</small></article></div>
<div class="dashboard-actions"><a class="action-card" href="{{ route('quizzes.index') }}"><span>📚</span><strong>Mis cuestionarios</strong><small>Crear, editar y publicar preguntas</small><b>Administrar →</b></a><a class="action-card" href="{{ route('rooms.create') }}"><span>🎮</span><strong>Crear una sala</strong><small>Elige un cuestionario y comparte su código</small><b>Crear partida →</b></a><a class="action-card" href="{{ route('rooms.index') }}"><span>🌎</span><strong>Salas públicas</strong><small>Descubre partidas abiertas</small><b>Explorar →</b></a></div>
@endsection
