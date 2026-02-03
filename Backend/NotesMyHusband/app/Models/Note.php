<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    protected $fillable = ['author_id', 'content'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'note_recipients', 'note_id', 'recipient_id')->withTimestamps();
    }
}
