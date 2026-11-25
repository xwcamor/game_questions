<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameRoom extends Model
{
    protected $fillable = ['quiz_id','host_id','title','code','visibility','password_hash','max_players','status','question_phase','current_question_index','question_order','option_orders','started_at','question_started_at','question_closed_at'];
    protected function casts(): array { return ['question_order'=>'array','option_orders'=>'array','started_at'=>'datetime','question_started_at'=>'datetime','question_closed_at'=>'datetime']; }
    public function quiz(): BelongsTo { return $this->belongsTo(Quiz::class); }
    public function host(): BelongsTo { return $this->belongsTo(User::class, 'host_id'); }
    public function participants(): HasMany { return $this->hasMany(GameParticipant::class); }
    public function answers(): HasMany { return $this->hasMany(GameAnswer::class); }
}
