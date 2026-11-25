<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title',160);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('time_limit_seconds')->default(20);
            $table->boolean('randomize_questions')->default(true);
            $table->boolean('randomize_answers')->default(true);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_template')->default(false);
            $table->timestamps();
            $table->index(['is_published','is_template']);
        });
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('category',100)->default('General');
            $table->text('question_text');
            $table->string('question_type',24)->default('single_choice');
            $table->text('explanation')->nullable();
            $table->unsignedSmallInteger('time_limit_seconds')->nullable();
            $table->unsignedInteger('points')->default(1000);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
            $table->index(['quiz_id','position']);
        });
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('option_text',500);
            $table->boolean('is_correct')->default(false);
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();
            $table->index(['question_id','position']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
