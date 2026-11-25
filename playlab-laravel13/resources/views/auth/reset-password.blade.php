@extends('layouts.app')
@section('title','Restablecer contraseña | PlayLab')
@section('content')
<div class="auth-wrap"><form class="form-card auth-card" method="POST" action="{{ route('password.update') }}">@csrf<input type="hidden" name="token" value="{{ $token }}"><span class="eyebrow">NUEVA CONTRASEÑA</span><h1>Restablece tu acceso</h1><label>Correo electrónico<input type="email" name="email" value="{{ old('email',$email) }}" required></label><label>Nueva contraseña<input type="password" name="password" required minlength="10"></label><label>Confirmar contraseña<input type="password" name="password_confirmation" required minlength="10"></label><button class="btn btn-primary btn-block">Guardar contraseña</button></form></div>
@endsection
