@extends('layouts.app')
@section('title','Salas públicas | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">ENCUENTRA UNA PARTIDA</span><h1>Salas públicas</h1><p class="muted">Únete a una sala que todavía está esperando jugadores.</p></div>@auth<a class="btn btn-primary" href="{{ route('rooms.create') }}">＋ Crear sala</a>@endauth</div>
<div class="room-grid">@forelse($rooms as $room)<article class="public-room-card"><span class="pill pill-green">ESPERANDO</span><h3>{{ $room->title }}</h3><p>{{ $room->quiz->title }}</p><div class="room-card-meta"><span>👥 {{ $room->participants_count }}/{{ $room->max_players }}</span><span>🎮 {{ $room->host->name }}</span></div><a class="btn btn-primary btn-block" href="{{ route('rooms.show',$room->code) }}">Entrar a sala →</a></article>@empty<div class="empty-state"><span>🌎</span><h3>No hay salas abiertas</h3><p>Cuando alguien cree una sala pública aparecerá aquí.</p></div>@endforelse</div>
@endsection
