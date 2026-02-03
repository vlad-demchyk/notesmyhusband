<?php

namespace App\GraphQL\Queries;

use App\Models\User;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class Me
{
    public function __invoke(mixed $_): User
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException('Unauthenticated.');
        }
        return $user;
    }
}
