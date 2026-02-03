<?php

namespace App\GraphQL\Mutations;

use App\Models\Note;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class DeleteNote
{
    public function __invoke(mixed $_, array $args): bool
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $note = Note::where('id', $args['id'])->where('author_id', $user->id)->first();
        if (!$note) {
            throw new AuthenticationException('Нотатку не знайдено або немає прав на видалення');
        }

        $note->delete();
        return true;
    }
}
