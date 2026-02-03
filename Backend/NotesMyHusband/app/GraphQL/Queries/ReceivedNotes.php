<?php

namespace App\GraphQL\Queries;

use App\Models\Note;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class ReceivedNotes
{
    /**
     * Нотатки, отримані поточним користувачем (виключаючи від заблокованих).
     */
    public function __invoke(mixed $_): Collection
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $blockedAuthorIds = $user->blockedUsers()->pluck('users.id')->toArray();

        return Note::query()
            ->whereHas('recipients', fn ($q) => $q->where('users.id', $user->id))
            ->whereNotIn('author_id', $blockedAuthorIds)
            ->with(['author', 'recipients'])
            ->orderByDesc('created_at')
            ->get();
    }
}
