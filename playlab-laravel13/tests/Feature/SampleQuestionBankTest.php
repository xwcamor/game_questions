<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleQuestionBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_contains_forty_six_option_questions(): void
    {
        $this->seed();
        $quiz = \App\Models\Quiz::where('title', 'Evaluación de Sistemas Operativos')->firstOrFail();
        $this->assertSame(40, $quiz->questions()->count());
        foreach ($quiz->questions as $question) {
            $this->assertSame(6, $question->options()->count());
            $this->assertSame(1, $question->options()->where('is_correct', true)->count());
        }
    }
}
