<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuizController
{
    public function index(): View
    {
        $user=Auth::user();
        $quizzes=Quiz::withCount('questions')
            ->when(($user->role ?? '') !== 'admin', fn($q)=>$q->where(fn($s)=>$s->where('user_id',$user->id)->orWhere('is_template',true)))
            ->latest()->paginate(12);
        return view('quizzes.index',compact('quizzes'));
    }

    public function create(): View { return view('quizzes.create'); }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'title'=>['required','string','max:160'],'description'=>['nullable','string','max:2000'],
            'time_limit_seconds'=>['required','integer','min:5','max:300'],
            'randomize_questions'=>['sometimes','boolean'],'randomize_answers'=>['sometimes','boolean'],
        ]);
        $data['user_id']=Auth::id();
        $data['randomize_questions']=$request->boolean('randomize_questions');
        $data['randomize_answers']=$request->boolean('randomize_answers');
        $data['is_published']=false;
        $quiz=Quiz::create($data);
        return redirect()->route('questions.create',$quiz)->with('success','Cuestionario creado. Agrega tus preguntas.');
    }

    public function edit(Quiz $quiz): View
    {
        abort_unless($quiz->user_id===Auth::id() || ($quiz->is_template && ($user=Auth::user())->role==='admin'),403);
        return view('quizzes.edit',compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        abort_unless($quiz->user_id===Auth::id() || (Auth::user()->role ?? '')==='admin',403);
        $data=$request->validate([
            'title'=>['required','string','max:160'],'description'=>['nullable','string','max:2000'],
            'time_limit_seconds'=>['required','integer','min:5','max:300'],
            'randomize_questions'=>['sometimes','boolean'],'randomize_answers'=>['sometimes','boolean'],
            'is_published'=>['sometimes','boolean'],
        ]);
        $data['randomize_questions']=$request->boolean('randomize_questions');
        $data['randomize_answers']=$request->boolean('randomize_answers');
        $data['is_published']=$request->boolean('is_published');
        $quiz->update($data);
        return redirect()->route('quizzes.index')->with('success','Cuestionario actualizado.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        abort_unless($quiz->user_id===Auth::id() || (Auth::user()->role ?? '')==='admin',403);
        abort_if($quiz->is_template,403,'La plantilla de demostración está protegida.');
        $quiz->delete();
        return back()->with('success','Cuestionario eliminado.');
    }
}
