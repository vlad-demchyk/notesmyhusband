<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class Login
{
    public function __invoke(mixed $_, array $args): array
    {
        $login = $args['login'] ?? '';
        $password = $args['password'] ?? '';

        if (empty($login) || empty($password)) {
            throw new AuthenticationException('Логін та пароль обов\'язкові');
        }

        $user = User::where('login', $login)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Невірний логін або пароль');
        }

        $token = $user->createToken('auth')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}
