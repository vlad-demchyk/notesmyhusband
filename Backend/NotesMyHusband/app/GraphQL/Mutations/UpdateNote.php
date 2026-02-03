<?php

namespace App\GraphQL\Mutations;

use App\Models\Note;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class UpdateNote
{
    public function __invoke(mixed $_, array $args): Note
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $note = Note::where('id', $args['id'])->where('author_id', $user->id)->first();
        if (!$note) {
            throw new AuthenticationException('Нотатку не знайдено або немає прав на редагування');
        }

        $content = $args['content'] ?? '';
        if (empty(trim($content))) {
            throw new AuthenticationException('Контент нотатки не може бути порожнім');
        }

        $note->update(['content' => $content]);
        return $note->fresh(['author', 'recipients']);
    }
}
