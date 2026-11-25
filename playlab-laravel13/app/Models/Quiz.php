<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = ['user_id','title','description','time_limit_seconds','randomize_questions','randomize_answers','is_published','is_template'];
    protected function casts(): array { return ['randomize_questions'=>'boolean','randomize_answers'=>'boolean','is_published'=>'boolean','is_template'=>'boolean']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function questions(): HasMany { return $this->hasMany(Question::class)->orderBy('position'); }
    public function rooms(): HasMany { return $this->hasMany(GameRoom::class); }
}
