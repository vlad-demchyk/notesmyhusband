<?php

namespace App\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Database\Database;

class Auth
{
    private static $secretKey = 'your-secret-key-change-this-in-production';
    private static $algorithm = 'HS256';

    public static function generateToken($userId, $login)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + (60 * 60 * 24 * 7); // 7 днів

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user_id' => $userId,
            'login' => $login
        ];

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function verifyToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function getCurrentUser()
    {
        // Підтримка різних способів отримання заголовків
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $key => $value) {
                if (strpos($key, 'HTTP_') === 0) {
                    $headerKey = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$headerKey] = $value;
                }
            }
        }
        
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if (!$authHeader) {
            return null;
        }

        // Перевіряємо формат "Bearer TOKEN"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
            return self::verifyToken($token);
        }

        return null;
    }

    public static function requireAuth()
    {
        $user = self::getCurrentUser();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        return $user;
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function login($login, $password): array | null
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, login, password FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && self::verifyPassword($password, $user['password'])) {
            return [
                'token' => self::generateToken($user['id'], $user['login']),
                'user' => [
                    'id' => $user['id'],
                    'login' => $user['login']
                ]
            ];
        }

        return null;
    }
}
