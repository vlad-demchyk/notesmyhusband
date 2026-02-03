<?php

namespace App\GraphQL\Queries;

use App\Models\Note;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class NoteById
{
    /**
     * Одна нотатка за ID: доступна автору або одному з отримувачів.
     */
    public function __invoke(mixed $_, array $args): ?Note
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $note = Note::query()
            ->with(['author', 'recipients'])
            ->find($args['id'] ?? null);

        if (!$note) {
            return null;
        }

        $isAuthor = (int) $note->author_id === (int) $user->id;
        $isRecipient = $note->recipients()->where('users.id', $user->id)->exists();

        if (!$isAuthor && !$isRecipient) {
            throw new AuthenticationException('Доступ до нотатки заборонено.');
        }

        return $note;
    }
}
