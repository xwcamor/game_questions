@extends('layouts.app')
@section('title','Cuestionarios | PlayLab')
@section('content')
<div class="page-heading"><div><span class="eyebrow">TU BIBLIOTECA</span><h1>Cuestionarios</h1><p class="muted">Prepara preguntas y crea partidas con tu contenido.</p></div><a class="btn btn-primary" href="{{ route('quizzes.create') }}">＋ Nuevo cuestionario</a></div>
<div class="quiz-list">
@forelse($quizzes as $quiz)<article class="quiz-card"><div class="quiz-card-icon">📚</div><div class="quiz-card-main"><div class="quiz-card-title"><h3>{{ $quiz->title }}</h3>@if($quiz->is_template)<span class="pill pill-purple">DEMOSTRACIÓN</span>@endif@if($quiz->is_published)<span class="pill pill-green">PUBLICADO</span>@else<span class="pill">BORRADOR</span>@endif</div><p>{{ $quiz->description ?: 'Sin descripción' }}</p><div class="quiz-meta"><span>{{ $quiz->questions_count }} preguntas</span><span>{{ $quiz->time_limit_seconds }} s por pregunta</span></div></div><div class="quiz-card-actions"><a class="btn btn-outline btn-sm" href="{{ route('quizzes.edit',$quiz) }}">Editar</a>@if($quiz->is_published)<a class="btn btn-primary btn-sm" href="{{ route('rooms.create',['quiz_id'=>$quiz->id]) }}">Jugar</a>@endif</div></article>@empty<div class="empty-state"><span>📝</span><h3>Aún no tienes cuestionarios</h3><p>Crea el primero o prueba la plantilla de demostración.</p><a class="btn btn-primary" href="{{ route('quizzes.create') }}">Crear cuestionario</a></div>@endforelse
</div>{{ $quizzes->links() }}
@endsection
