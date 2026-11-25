<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameParticipant extends Model
{
    protected $fillable = ['game_room_id', 'user_id', 'display_name', 'avatar', 'session_token', 'score', 'joined_at', 'last_seen_at'];
    protected function casts(): array { return ['joined_at' => 'datetime', 'last_seen_at' => 'datetime']; }
    public function room(): BelongsTo { return $this->belongsTo(GameRoom::class, 'game_room_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function answers(): HasMany { return $this->hasMany(GameAnswer::class); }
}
