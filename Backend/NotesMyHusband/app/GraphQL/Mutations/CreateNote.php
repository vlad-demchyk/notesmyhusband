<?php

namespace App\GraphQL\Mutations;

use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class CreateNote
{
    public function __invoke(mixed $_, array $args): Note
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $content = $args['content'] ?? '';
        if (empty(trim($content))) {
            throw new AuthenticationException('Контент нотатки не може бути порожнім');
        }

        $recipientIds = $args['recipient_ids'] ?? [];

        if (!empty($recipientIds)) {
            $blocked = DB::table('blocked_users')
                ->whereIn('user_id', $recipientIds)
                ->where('blocked_user_id', $user->id)
                ->exists();
            if ($blocked) {
                throw new AuthenticationException('Не можна відправити нотатку користувачу, який заблокував вас');
            }
        }

        $note = Note::create([
            'author_id' => $user->id,
            'content' => $content,
        ]);

        foreach ($recipientIds as $recipientId) {
            if ((int) $recipientId !== (int) $user->id) {
                $note->recipients()->attach($recipientId);
            }
        }

        return $note->fresh(['author', 'recipients']);
    }
}
