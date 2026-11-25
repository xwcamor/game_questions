@extends('layouts.app')
@section('title','Recuperar contraseña | PlayLab')
@section('content')
<div class="auth-wrap"><form class="form-card auth-card" method="POST" action="{{ route('password.email') }}">@csrf<span class="eyebrow">RECUPERACIÓN DE CUENTA</span><h1>¿Olvidaste tu contraseña?</h1><p class="muted">Te enviaremos un enlace para restablecerla.</p><label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" required></label><button class="btn btn-primary btn-block">Enviar enlace</button>@if(session('status'))<p class="notice notice-success">{{ session('status') }}</p>@endif<a class="text-link" href="{{ route('login') }}">Volver al ingreso</a></form></div>
@endsection
