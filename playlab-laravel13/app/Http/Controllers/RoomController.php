<?php

namespace App\Http\Controllers;

use App\Models\GameAnswer;
use App\Models\GameParticipant;
use App\Models\GameRoom;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoomController
{
    public function index(): View
    {
        $rooms=GameRoom::with(['quiz','host'])->withCount('participants')->where('visibility','public')->where('status','lobby')->latest()->limit(30)->get();
        return view('rooms.index',compact('rooms'));
    }

    public function create(): View
    {
        $user=Auth::user();
        $quizzes=Quiz::query()->where('is_published',true)
            ->when(($user->role ?? '')!=='admin',fn($q)=>$q->where(fn($s)=>$s->where('user_id',$user->id)->orWhere('is_template',true)))
            ->withCount('questions')->having('questions_count','>',0)->orderBy('title')->get();
        return view('rooms.create',compact('quizzes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'quiz_id'=>['required','integer','exists:quizzes,id'],'title'=>['required','string','max:120'],
            'visibility'=>['required','in:public,private'],'password'=>['nullable','string','min:4','max:64'],
            'max_players'=>['required','integer','min:2','max:200'],
        ]);
        $quiz=Quiz::with('questions.options')->findOrFail($data['quiz_id']);
        abort_unless($quiz->is_published,403);
        abort_unless($quiz->user_id===Auth::id() || $quiz->is_template || (Auth::user()->role ?? '')==='admin',403);
        abort_if($quiz->questions->isEmpty(),422,'Agrega preguntas antes de crear una sala.');
        $questionIds=$quiz->questions->pluck('id')->all();
        if($quiz->randomize_questions) shuffle($questionIds);
        $optionOrders=[];
        foreach($quiz->questions as $question){
            $ids=$question->options->pluck('id')->all();
            if($quiz->randomize_answers) shuffle($ids);
            $optionOrders[(string)$question->id]=$ids;
        }
        do{$code=Str::upper(Str::random(6));}while(GameRoom::where('code',$code)->exists());
        $room=GameRoom::create([
            'quiz_id'=>$quiz->id,'host_id'=>Auth::id(),'title'=>$data['title'],'code'=>$code,
            'visibility'=>$data['visibility'],'password_hash'=>$data['visibility']==='private' && filled($data['password'] ?? null)?Hash::make($data['password']):null,
            'max_players'=>$data['max_players'],'status'=>'lobby','question_phase'=>'question',
            'current_question_index'=>0,'question_order'=>$questionIds,'option_orders'=>$optionOrders,
        ]);
        return redirect()->route('rooms.show',$room->code)->with('success','Sala creada. Comparte el código con tus participantes.');
    }

    public function show(string $code): View
    {
        $room=$this->findRoom($code);
        $isHost=$this->isHost($room);
        $participant=$this->participantFor($room);
        if(!$isHost && !$participant) return view('rooms.join',compact('room'));
        return view('rooms.show',compact('room','isHost','participant'));
    }

    public function join(Request $request): RedirectResponse
    {
        $data=$request->validate(['code'=>['required','string','size:6'],'display_name'=>['required','string','max:40'],'password'=>['nullable','string','max:64'],'avatar'=>['nullable','string','max:12']]);
        $room=$this->findRoom($data['code']);
        if($room->status!=='lobby') return back()->withErrors(['code'=>'Esta partida ya comenzó o terminó.']);
        if($room->visibility==='private' && $room->password_hash && !Hash::check($data['password'] ?? '',$room->password_hash)) return back()->withErrors(['password'=>'La contraseña no es correcta.']);
        if($room->participants()->count()>=$room->max_players) return back()->withErrors(['code'=>'La sala alcanzó su límite de participantes.']);
        $token=Str::random(64);
        $participant=$room->participants()->create(['user_id'=>Auth::id(),'display_name'=>$data['display_name'],'avatar'=>$data['avatar'] ?? '🎮','session_token'=>hash('sha256',$token),'score'=>0,'joined_at'=>now(),'last_seen_at'=>now()]);
        $request->session()->put('playlab_participants.'.$room->code,$token);
        return redirect()->route('rooms.show',$room->code);
    }

    public function state(Request $request,string $code): JsonResponse
    {
        $room=$this->findRoom($code);
        $isHost=$this->isHost($room);
        $participant=$this->participantFor($room);
        abort_unless($isHost || $participant,403);
        if($participant) $participant->forceFill(['last_seen_at'=>now()])->save();
        $room->load(['participants'=>fn($q)=>$q->orderByDesc('score')->orderBy('id')]);
        $data=[
            'code'=>$room->code,'title'=>$room->title,'status'=>$room->status,'phase'=>$room->question_phase,
            'current_index'=>$room->current_question_index,'total_questions'=>count($room->question_order ?? []),
            'participants'=>$room->participants->map(fn($p)=>['id'=>$p->id,'name'=>$p->display_name,'avatar'=>$p->avatar,'score'=>$p->score]),
            'is_host'=>$isHost,'my_participant_id'=>$participant?->id,
            'started_at'=>$room->question_started_at?->toIso8601String(),
            'closed_at'=>$room->question_closed_at?->toIso8601String(),
        ];
        if($room->status==='running' && $room->question_phase==='question'){
            $question=$this->currentQuestion($room);
            if($question){
                $order=$room->option_orders[(string)$question->id] ?? $question->options->pluck('id')->all();
                $options=QuestionOption::whereIn('id',$order)->get()->keyBy('id');
                $data['question']=[
                    'text'=>$question->question_text,'category'=>$question->category,
                    'time_limit'=>$question->time_limit_seconds ?: $room->quiz->time_limit_seconds,
                    'options'=>collect($order)->map(fn($id)=>['id'=>$id,'text'=>$options[$id]->option_text ?? ''])->values(),
                ];
            }
        } elseif($room->question_phase==='reveal'){
            $question=$this->currentQuestion($room);
            if($question){
                $data['question']=['text'=>$question->question_text,'category'=>$question->category,'explanation'=>$question->explanation,
                    'correct_option_ids'=>$question->options()->where('is_correct',true)->pluck('id'),
                    'answer_summary'=>$this->answerSummary($room,$question)];
            }
        }
        $data['leaderboard']=$room->participants->take(10)->values()->map(fn($p,$i)=>['position'=>$i+1,'name'=>$p->display_name,'avatar'=>$p->avatar,'score'=>$p->score]);
        return response()->json($data);
    }

    public function answer(Request $request,string $code): JsonResponse
    {
        $room=$this->findRoom($code);
        abort_unless($room->status==='running' && $room->question_phase==='question',409,'La pregunta ya no está recibiendo respuestas.');
        $participant=$this->participantFor($room);
        abort_unless($participant,403);
        $question=$this->currentQuestion($room);
        abort_unless($question,409);
        $data=$request->validate(['option_ids'=>['required','array','min:1','max:6'],'option_ids.*'=>['required','integer','distinct','exists:question_options,id']]);
        $selected=collect($data['option_ids'])->map(fn($v)=>(int)$v)->sort()->values();
        $valid=$question->options()->pluck('id')->map(fn($v)=>(int)$v)->sort()->values();
        abort_unless($selected->diff($valid)->isEmpty(),422,'Alternativa no válida para esta pregunta.');
        $existing=GameAnswer::where('game_room_id',$room->id)->where('game_participant_id',$participant->id)->where('question_id',$question->id)->exists();
        abort_if($existing,409,'Ya respondiste esta pregunta.');
        $started=$room->question_started_at ?? now();
        $timeLimit=$question->time_limit_seconds ?: $room->quiz->time_limit_seconds;
        $elapsed=max(0,(int)round($started->diffInMilliseconds(now())));
        if($elapsed>$timeLimit*1000) abort(409,'Se acabó el tiempo.');
        $correct=$question->options()->where('is_correct',true)->pluck('id')->map(fn($v)=>(int)$v)->sort()->values();
        $isCorrect=$selected->values()->all()===$correct->values()->all();
        $points=$isCorrect ? max(0,(int)round($question->points * (1-min(1,$elapsed/($timeLimit*1000))))) : 0;
        DB::transaction(function()use($room,$participant,$question,$selected,$isCorrect,$points,$elapsed){
            GameAnswer::create(['game_room_id'=>$room->id,'game_participant_id'=>$participant->id,'question_id'=>$question->id,'selected_option_ids'=>$selected->all(),'is_correct'=>$isCorrect,'points_awarded'=>$points,'response_time_ms'=>$elapsed,'answered_at'=>now()]);
            $participant->increment('score',$points);
        });
        return response()->json(['saved'=>true,'is_correct'=>$isCorrect,'points_awarded'=>$points]);
    }

    public function start(string $code): RedirectResponse
    {
        $room=$this->findRoom($code);$this->authorizeHost($room);
        abort_unless($room->status==='lobby',409);
        $room->update(['status'=>'running','question_phase'=>'question','current_question_index'=>0,'started_at'=>now(),'question_started_at'=>now(),'question_closed_at'=>null]);
        return back()->with('success','¡Partida iniciada!');
    }

    public function reveal(string $code): RedirectResponse
    {
        $room=$this->findRoom($code);$this->authorizeHost($room);
        abort_unless($room->status==='running',409);
        $room->update(['question_phase'=>'reveal','question_closed_at'=>now()]);
        return back();
    }

    public function next(string $code): RedirectResponse
    {
        $room=$this->findRoom($code);$this->authorizeHost($room);
        abort_unless($room->status==='running' && $room->question_phase==='reveal',409);
        $next=$room->current_question_index+1;
        if($next>=count($room->question_order ?? [])){
            $room->update(['status'=>'finished','question_phase'=>'finished']);
        }else{
            $room->update(['current_question_index'=>$next,'question_phase'=>'question','question_started_at'=>now(),'question_closed_at'=>null]);
        }
        return back();
    }

    public function finish(string $code): RedirectResponse
    {
        $room=$this->findRoom($code);$this->authorizeHost($room);
        $room->update(['status'=>'finished','question_phase'=>'finished','question_closed_at'=>now()]);
        return back()->with('success','Partida finalizada.');
    }

    private function findRoom(string $code): GameRoom { return GameRoom::with(['quiz.questions.options'])->where('code',Str::upper($code))->firstOrFail(); }
    private function isHost(GameRoom $room): bool { return Auth::check() && (Auth::id()===$room->host_id || (Auth::user()->role ?? '')==='admin'); }
    private function authorizeHost(GameRoom $room): void { abort_unless($this->isHost($room),403); }
    private function participantFor(GameRoom $room): ?GameParticipant
    {
        $token=session('playlab_participants.'.$room->code);
        if(!$token) return null;
        return $room->participants()->where('session_token',hash('sha256',$token))->first();
    }
    private function currentQuestion(GameRoom $room): ?Question
    {
        $id=$room->question_order[$room->current_question_index] ?? null;
        return $id ? Question::with('options')->find($id) : null;
    }
    private function answerSummary(GameRoom $room,Question $question): array
    {
        return $room->answers()->where('question_id',$question->id)->with('participant')->get()
            ->map(fn($answer)=>['name'=>$answer->participant->display_name,'correct'=>$answer->is_correct,'points'=>$answer->points_awarded,'time_ms'=>$answer->response_time_ms])
            ->sortBy('time_ms')->values()->all();
    }
}
