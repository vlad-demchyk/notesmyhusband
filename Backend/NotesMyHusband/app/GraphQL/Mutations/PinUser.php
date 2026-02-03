<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class PinUser
{
    public function __invoke(mixed $_, array $args): bool
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $userId = (int) $args['user_id'];
        if ($userId === (int) $user->id) {
            throw new AuthenticationException('Не можна запінити самого себе');
        }

        DB::table('pinned_users')->insertOrIgnore([
            'user_id' => $user->id,
            'pinned_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return true;
    }
}
