@extends('layouts.app')
@section('title','Editar pregunta | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">{{ $question->quiz->title }}</span><h1>Editar pregunta</h1></div></div>
<form class="form-card form-wide" method="POST" action="{{ route('questions.update',$question) }}">@csrf @method('PUT') @include('questions._form') </form>
@endsection
