<?php

namespace App\GraphQL\Mutations;

use Illuminate\Support\Facades\DB;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class UnblockUser
{
    public function __invoke(mixed $_, array $args): bool
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        DB::table('blocked_users')
            ->where('user_id', $user->id)
            ->where('blocked_user_id', (int) $args['user_id'])
            ->delete();
        return true;
    }
}
