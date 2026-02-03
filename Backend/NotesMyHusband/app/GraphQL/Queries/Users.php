<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class Users
{
    /**
     * Список користувачів: поточний та заблоковані виключаються.
     */
    public function __invoke(mixed $_): Collection
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $blockedIds = $user->blockedUsers()->pluck('users.id')->toArray();

        return User::query()
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $blockedIds)
            ->orderBy('login')
            ->get();
    }
}
