<?php

namespace App\GraphQL\Queries;

use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class MyNotes
{
    /**
     * Нотатки, автором яких є поточний користувач.
     */
    public function __invoke(mixed $_): Collection
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return Note::query()
            ->where('author_id', $user->id)
            ->with(['author', 'recipients'])
            ->orderByDesc('created_at')
            ->get();
    }
}
