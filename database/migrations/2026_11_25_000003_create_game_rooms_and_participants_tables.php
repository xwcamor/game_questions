<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('game_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->string('title',120);
            $table->string('code',6)->unique();
            $table->string('visibility',12)->default('private');
            $table->string('password_hash')->nullable();
            $table->unsignedSmallInteger('max_players')->default(50);
            $table->string('status',16)->default('lobby');
            $table->string('question_phase',16)->default('question');
            $table->unsignedInteger('current_question_index')->default(0);
            $table->json('question_order');
            $table->json('option_orders');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('question_started_at')->nullable();
            $table->timestamp('question_closed_at')->nullable();
            $table->timestamps();
            $table->index(['status','visibility']);
        });
        Schema::create('game_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name',40);
            $table->string('avatar',12)->default('🎮');
            $table->string('session_token',64)->unique();
            $table->unsignedBigInteger('score')->default(0);
            $table->timestamp('joined_at');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->index(['game_room_id','score']);
        });
        Schema::create('game_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('selected_option_ids');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('points_awarded')->default(0);
            $table->unsignedInteger('response_time_ms')->default(0);
            $table->timestamp('answered_at');
            $table->timestamps();
            $table->unique(['game_room_id','game_participant_id','question_id'],'game_answer_unique');
            $table->index(['game_room_id','question_id','is_correct']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('game_answers');
        Schema::dropIfExists('game_participants');
        Schema::dropIfExists('game_rooms');
    }
};
