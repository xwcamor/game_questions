@extends('layouts.app')
@section('title',$room->title.' | PlayLab')
@section('content')
<div id="game-app" class="game-shell" data-room-code="{{ $room->code }}" data-is-host="{{ $isHost?'1':'0' }}" data-state-url="{{ route('rooms.state',$room->code) }}" data-answer-url="{{ route('rooms.answer',$room->code) }}" data-start-url="{{ route('rooms.start',$room->code) }}" data-reveal-url="{{ route('rooms.reveal',$room->code) }}" data-next-url="{{ route('rooms.next',$room->code) }}" data-finish-url="{{ route('rooms.finish',$room->code) }}">
<div class="game-topbar"><div><a class="back-link" href="{{ $isHost?route('dashboard'):route('home') }}">← Salir</a><h1>{{ $room->title }}</h1></div><div class="game-top-actions"><span class="room-code-chip">CÓDIGO <strong>{{ $room->code }}</strong></span><button id="sound-toggle" class="btn btn-outline btn-sm" type="button">🔊 Sonido</button></div></div>
<div id="game-stage"><div class="loading-game"><div class="loading-spinner"></div><strong>Conectando con la sala...</strong><small>Preparando la partida</small></div></div>
</div>
@endsection
