@extends('layouts.app')
@section('title','Agregar pregunta | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ $quiz->title }}</span><h1>Nueva pregunta</h1><p class="muted">Completa el enunciado y marca la(s) alternativa(s) correcta(s).</p></div></div>
<form class="form-card form-wide" method="POST" action="{{ route('questions.store',$quiz) }}">@csrf @include('questions._form') </form>
@endsection
