<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController
{
    public function create(Quiz $quiz): View
    {
        $this->authorizeQuiz($quiz);
        return view('questions.create',compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuiz($quiz);
        $data=$this->validateQuestion($request);
        $question=$quiz->questions()->create([
            'category'=>$data['category'] ?? 'General','question_text'=>$data['question_text'],
            'question_type'=>$data['question_type'],'explanation'=>$data['explanation'] ?? null,
            'time_limit_seconds'=>$data['time_limit_seconds'] ?? null,'points'=>$data['points'] ?? 1000,
            'position'=>(int)$quiz->questions()->max('position')+1,
        ]);
        foreach ($data['options'] as $index=>$option) {
            $question->options()->create([
                'option_text'=>$option['text'],'is_correct'=>in_array((string)$index,$data['correct'] ?? [],true),
                'position'=>$index+1,
            ]);
        }
        return redirect()->route('quizzes.edit',$quiz)->with('success','Pregunta agregada.');
    }

    public function edit(Question $question): View
    {
        $this->authorizeQuiz($question->quiz);
        $question->load('options');
        return view('questions.edit',compact('question'));
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $this->authorizeQuiz($question->quiz);
        $data=$this->validateQuestion($request);
        $question->update([
            'category'=>$data['category'] ?? 'General','question_text'=>$data['question_text'],
            'question_type'=>$data['question_type'],'explanation'=>$data['explanation'] ?? null,
            'time_limit_seconds'=>$data['time_limit_seconds'] ?? null,'points'=>$data['points'] ?? 1000,
        ]);
        $question->options()->delete();
        foreach ($data['options'] as $index=>$option) {
            $question->options()->create(['option_text'=>$option['text'],'is_correct'=>in_array((string)$index,$data['correct'] ?? [],true),'position'=>$index+1]);
        }
        return redirect()->route('quizzes.edit',$question->quiz)->with('success','Pregunta actualizada.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $this->authorizeQuiz($question->quiz);
        $quiz=$question->quiz;
        $question->delete();
        return back()->with('success','Pregunta eliminada.');
    }

    private function authorizeQuiz(Quiz $quiz): void
    {
        abort_unless($quiz->user_id===Auth::id() || $quiz->is_template || (Auth::user()->role ?? '')==='admin',403);
    }

    private function validateQuestion(Request $request): array
    {
        $data=$request->validate([
            'category'=>['nullable','string','max:100'],'question_text'=>['required','string','max:2000'],
            'question_type'=>['required','in:single_choice,multiple_choice,true_false'],
            'explanation'=>['nullable','string','max:2000'],'time_limit_seconds'=>['nullable','integer','min:5','max:300'],
            'points'=>['nullable','integer','min:100','max:5000'],
            'options'=>['required','array','min:2','max:6'],
            'options.*.text'=>['required','string','max:500'],
            'correct'=>['required','array','min:1'],'correct.*'=>['required','integer','min:0','max:5'],
        ]);
        $optionCount=count($data['options']);
        $correct=array_unique(array_map('strval',$data['correct']));
        foreach ($correct as $index) {
            if ((int)$index >= $optionCount) throw ValidationException::withMessages(['correct'=>'La respuesta correcta seleccionada no existe.']);
        }
        if ($data['question_type']==='single_choice' && count($correct)!==1) {
            throw ValidationException::withMessages(['correct'=>'Selecciona exactamente una respuesta correcta.']);
        }
        if ($data['question_type']==='true_false' && $optionCount!==2) {
            throw ValidationException::withMessages(['options'=>'Verdadero/falso debe tener exactamente dos alternativas.']);
        }
        $data['correct']=$correct;
        return $data;
    }
}
