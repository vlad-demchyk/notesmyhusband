<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'login',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            // Пароль хешується вручну в мутаціях через Hash::make()
            // 'password' => 'hashed', // Видалено, щоб уникнути подвійного хешування
        ];
    }

    public function notes()
    {
        return $this->hasMany(Note::class, 'author_id');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'user_id', 'blocked_user_id');
    }

    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'blocked_user_id', 'user_id');
    }

    public function pinnedUsers()
    {
        return $this->belongsToMany(User::class, 'pinned_users', 'user_id', 'pinned_user_id');
    }

    public function receivedNotes()
    {
        return $this->belongsToMany(Note::class, 'note_recipients', 'recipient_id', 'note_id');
    }
}
