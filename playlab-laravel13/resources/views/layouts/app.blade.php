<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PlayLab | Kahoot')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body><div class="app-shell">
<header class="topbar">
<a class="brand" href="{{ route('home') }}"><span class="brand-mark">✦</span><span>play<span class="brand-accent">lab</span></span></a>
<nav class="main-nav"><a href="{{ route('rooms.index') }}">Salas públicas</a>@auth<a href="{{ route('dashboard') }}">Panel</a><a href="{{ route('quizzes.index') }}">Cuestionarios</a><a href="{{ route('rooms.create') }}">Crear sala</a>@endauth</nav>
<div class="nav-actions">@auth
<span class="user-chip"><span class="avatar-dot">{{ mb_substr(auth()->user()->name,0,1) }}</span><span>{{ auth()->user()->name }}</span>@if((auth()->user()->role ?? '')==='admin')<span class="admin-tag">ADMIN</span>@endif</span>
<form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-ghost btn-sm">Salir</button></form>
@else<a class="btn btn-ghost btn-sm" href="{{ route('login') }}">Ingresar</a><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Crear cuenta</a>@endauth</div>
</header>
@if(session('success'))<div class="flash flash-success">✓ {{ session('success') }}</div>@endif
@if($errors->any())<div class="flash flash-error"><strong>Revisa estos datos:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<main class="page-content">@yield('content')</main>
<footer class="site-footer"><span>✦ PlayLab</span><span>Aprender también puede ser una competencia divertida.</span></footer>
</div></body></html>
