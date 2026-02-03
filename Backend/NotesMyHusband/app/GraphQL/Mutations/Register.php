<?php

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Nuwave\Lighthouse\Exceptions\AuthenticationException;

final readonly class Register
{
    public function __invoke(mixed $_, array $args): array
    {
        try {
            $login = $args['login'] ?? '';
            $password = $args['password'] ?? '';
            $email = $args['email'] ?? null;

            if (empty($login) || empty($password)) {
                throw new AuthenticationException('Логін та пароль обов\'язкові');
            }

            if (User::where('login', $login)->exists()) {
                throw new AuthenticationException('Користувач з таким логіном вже існує');
            }

            // Перевіряємо, чи існує колонка email в таблиці users
            $hasEmailColumn = Schema::hasColumn('users', 'email');

            // Перевіряємо email тільки якщо він переданий і поле існує в БД
            if ($email && $hasEmailColumn) {
                if (User::where('email', $email)->exists()) {
                    throw new AuthenticationException('Користувач з таким email вже існує');
                }
            }

            $userData = [
                'login' => $login,
                'password' => Hash::make($password),
            ];

            // Додаємо email тільки якщо він переданий і колонка існує
            if ($email && $hasEmailColumn) {
                $userData['email'] = $email;
            }

            $user = User::create($userData);
            
            // Перезавантажуємо користувача для отримання всіх полів
            $user->refresh();

            $token = $user->createToken('auth')->plainTextToken;

            return [
                'token' => $token,
                'user' => $user,
            ];
        } catch (AuthenticationException $e) {
            // Перекидаємо AuthenticationException без змін
            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            // Помилки БД
            \Log::error('Register DB error: ' . $e->getMessage(), [
                'sql' => $e->getSql() ?? 'N/A',
                'bindings' => $e->getBindings() ?? []
            ]);
            throw new AuthenticationException('Помилка бази даних при реєстрації: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Інші помилки
            \Log::error('Register mutation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new AuthenticationException('Помилка реєстрації: ' . $e->getMessage());
        }
    }
}
