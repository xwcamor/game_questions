<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAnswer extends Model
{
    protected $fillable = [
        'game_room_id', 'game_participant_id', 'question_id',
        'selected_option_ids', 'is_correct', 'points_awarded',
        'response_time_ms', 'answered_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_option_ids' => 'array',
            'is_correct' => 'boolean',
            'answered_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo { return $this->belongsTo(GameRoom::class, 'game_room_id'); }
    public function participant(): BelongsTo { return $this->belongsTo(GameParticipant::class, 'game_participant_id'); }
    public function question(): BelongsTo { return $this->belongsTo(Question::class); }
}
