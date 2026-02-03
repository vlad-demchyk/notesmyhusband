<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class PinnedUsers
{
    public function __invoke(mixed $_): Collection
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $user->pinnedUsers()->orderBy('login')->get();
    }
}
