@extends('layouts.app')
@section('title','Ingresar | PlayLab')
@section('content')
<div class="auth-wrap"><form class="form-card auth-card" method="POST" action="{{ url('/ingresar') }}">@csrf<span class="eyebrow">QUÉ BUENO VERTE</span><h1>Inicia sesión</h1><p class="muted">Vuelve a tus cuestionarios y partidas.</p><label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label><label>Contraseña<input type="password" name="password" required autocomplete="current-password"></label><label class="check-line"><input type="checkbox" name="remember" value="1"> Recordarme</label><button class="btn btn-primary btn-block btn-lg">Ingresar →</button><a class="text-link" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a><p class="muted center">¿No tienes cuenta? <a class="text-link" href="{{ route('register') }}">Regístrate</a></p></form></div>
@endsection
