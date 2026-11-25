<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KahootDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email'=>'admin@kahoot.local'], ['name'=>'Administrador Kahoot','password'=>Hash::make('DemoAdmin2026!')]);
        $admin->forceFill(['role'=>'admin'])->save();
        $quiz = Quiz::firstOrCreate(['user_id'=>$admin->id,'title'=>'Evaluación de Sistemas Operativos'],[
            'description'=>'Banco de demostración: 40 preguntas con seis alternativas y distractores.',
            'time_limit_seconds'=>20,'randomize_questions'=>true,'randomize_answers'=>true,'is_published'=>true,'is_template'=>true,
        ]);
        if ($quiz->questions()->exists()) return;
        $items=json_decode(file_get_contents(__DIR__.'/data/sample_questions.json'),true,512,JSON_THROW_ON_ERROR);
        foreach ($items as $index=>$item) {
            $question=$quiz->questions()->create(['category'=>$item['c'],'question_text'=>$item['q'],'question_type'=>'single_choice','explanation'=>null,'time_limit_seconds'=>null,'points'=>1000,'position'=>$index+1]);
            foreach ($item['a'] as $optionIndex=>$optionText) $question->options()->create(['option_text'=>$optionText,'is_correct'=>$optionIndex===(int)$item['k'],'position'=>$optionIndex+1]);
        }
    }
}
