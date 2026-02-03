<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class BlockUser
{
    public function __invoke(mixed $_, array $args): bool
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $userId = (int) $args['user_id'];
        if ($userId === (int) $user->id) {
            throw new AuthenticationException('Не можна заблокувати самого себе');
        }

        DB::table('blocked_users')->insertOrIgnore([
            'user_id' => $user->id,
            'blocked_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('pinned_users')
            ->where('user_id', $user->id)
            ->where('pinned_user_id', $userId)
            ->delete();
        return true;
    }
}
