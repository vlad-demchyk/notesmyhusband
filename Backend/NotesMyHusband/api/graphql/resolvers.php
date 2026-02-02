<?php

namespace App\graphql;

use App\Auth\Auth;
use App\Database\Database;

class Resolvers
{
    private static function getDb()
    {
        return Database::getInstance()->getConnection();
    }

    public static function getCurrentUser()
    {
        $userData = Auth::getCurrentUser();
        if (!$userData) {
            return null;
        }

        $db = self::getDb();
        $stmt = $db->prepare("SELECT id, login, created_at FROM users WHERE id = ?");
        $stmt->execute([$userData['user_id']]);
        return $stmt->fetch();
    }

    public static function getUserById($userId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("SELECT id, login, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public static function getAllUsers()
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        // Отримуємо список заблокованих користувачів
        $blockedStmt = $db->prepare("SELECT blocked_user_id FROM blocked_users WHERE user_id = ?");
        $blockedStmt->execute([$currentUser['user_id']]);
        $blockedIds = array_column($blockedStmt->fetchAll(), 'blocked_user_id');
        
        // Отримуємо всіх користувачів крім поточного та заблокованих
        $placeholders = str_repeat('?,', count($blockedIds) + 1);
        $placeholders = rtrim($placeholders, ',');
        $params = array_merge([$currentUser['user_id']], $blockedIds);
        
        $sql = "SELECT id, login, created_at FROM users WHERE id != ? AND id NOT IN ($placeholders)";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function register($login, $password)
    {
        $db = self::getDb();
        
        // Перевіряємо чи існує користувач
        $stmt = $db->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            throw new \Exception("Користувач з таким логіном вже існує");
        }

        $hashedPassword = Auth::hashPassword($password);
        $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
        $stmt->execute([$login, $hashedPassword]);
        
        $userId = $db->lastInsertId();
        $token = Auth::generateToken($userId, $login);

        return [
            'token' => $token,
            'user' => [
                'id' => $userId,
                'login' => $login,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }

    public static function login($login, $password)
    {
        $result = Auth::login($login, $password);
        if (!$result) {
            throw new \Exception("Невірний логін або пароль");
        }
        return $result;
    }

    public static function getMyNotes()
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("
            SELECT n.* FROM notes n
            WHERE n.author_id = ?
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$currentUser['user_id']]);
        return $stmt->fetchAll();
    }

    public static function getReceivedNotes()
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        // Перевіряємо чи автор не в чорному списку
        $stmt = $db->prepare("
            SELECT DISTINCT n.* FROM notes n
            INNER JOIN note_recipients nr ON n.id = nr.note_id
            LEFT JOIN blocked_users bu ON bu.user_id = ? AND bu.blocked_user_id = n.author_id
            WHERE nr.recipient_id = ? AND bu.id IS NULL
            ORDER BY n.created_at DESC
        ");
        $stmt->execute([$currentUser['user_id'], $currentUser['user_id']]);
        return $stmt->fetchAll();
    }

    public static function getNoteById($noteId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("
            SELECT n.* FROM notes n
            LEFT JOIN note_recipients nr ON n.id = nr.note_id
            WHERE n.id = ? AND (n.author_id = ? OR nr.recipient_id = ?)
        ");
        $stmt->execute([$noteId, $currentUser['user_id'], $currentUser['user_id']]);
        $note = $stmt->fetch();
        
        if (!$note) {
            throw new \Exception("Нотатку не знайдено або немає доступу");
        }
        
        return $note;
    }

    public static function getNoteRecipients($noteId)
    {
        $db = self::getDb();
        $stmt = $db->prepare("
            SELECT u.id, u.login, u.created_at 
            FROM users u
            INNER JOIN note_recipients nr ON u.id = nr.recipient_id
            WHERE nr.note_id = ?
        ");
        $stmt->execute([$noteId]);
        return $stmt->fetchAll();
    }

    public static function createNote($content, $recipientIds)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        if (empty($content)) {
            throw new \Exception("Контент нотатки не може бути порожнім");
        }

        // Перевіряємо чи отримувачі не в чорному списку
        if (!empty($recipientIds)) {
            $placeholders = str_repeat('?,', count($recipientIds));
            $placeholders = rtrim($placeholders, ',');
            $blockedStmt = $db->prepare("
                SELECT blocked_user_id FROM blocked_users 
                WHERE user_id IN ($placeholders) AND blocked_user_id = ?
            ");
            $blockedStmt->execute(array_merge($recipientIds, [$currentUser['user_id']]));
            if ($blockedStmt->fetch()) {
                throw new \Exception("Не можна відправити нотатку користувачу, який заблокував вас");
            }
        }

        // Створюємо нотатку
        $stmt = $db->prepare("INSERT INTO notes (author_id, content) VALUES (?, ?)");
        $stmt->execute([$currentUser['user_id'], $content]);
        $noteId = $db->lastInsertId();

        // Додаємо отримувачів
        if (!empty($recipientIds)) {
            $stmt = $db->prepare("INSERT INTO note_recipients (note_id, recipient_id) VALUES (?, ?)");
            foreach ($recipientIds as $recipientId) {
                if ($recipientId != $currentUser['user_id']) {
                    try {
                        $stmt->execute([$noteId, $recipientId]);
                    } catch (\PDOException $e) {
                        // Ігноруємо помилки дублікатів
                    }
                }
            }
        }

        // Повертаємо створену нотатку
        $stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$noteId]);
        return $stmt->fetch();
    }

    public static function updateNote($noteId, $content)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        // Перевіряємо чи користувач є автором
        $stmt = $db->prepare("SELECT * FROM notes WHERE id = ? AND author_id = ?");
        $stmt->execute([$noteId, $currentUser['user_id']]);
        $note = $stmt->fetch();
        
        if (!$note) {
            throw new \Exception("Нотатку не знайдено або немає прав на редагування");
        }

        if (empty($content)) {
            throw new \Exception("Контент нотатки не може бути порожнім");
        }

        $stmt = $db->prepare("UPDATE notes SET content = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$content, $noteId]);

        $stmt = $db->prepare("SELECT * FROM notes WHERE id = ?");
        $stmt->execute([$noteId]);
        return $stmt->fetch();
    }

    public static function deleteNote($noteId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        // Перевіряємо чи користувач є автором
        $stmt = $db->prepare("SELECT id FROM notes WHERE id = ? AND author_id = ?");
        $stmt->execute([$noteId, $currentUser['user_id']]);
        
        if (!$stmt->fetch()) {
            throw new \Exception("Нотатку не знайдено або немає прав на видалення");
        }

        $stmt = $db->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$noteId]);
        
        return true;
    }

    public static function getPinnedUsers()
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("
            SELECT u.id, u.login, u.created_at 
            FROM users u
            INNER JOIN pinned_users pu ON u.id = pu.pinned_user_id
            WHERE pu.user_id = ?
        ");
        $stmt->execute([$currentUser['user_id']]);
        return $stmt->fetchAll();
    }

    public static function pinUser($userId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        if ($userId == $currentUser['user_id']) {
            throw new \Exception("Не можна запінити самого себе");
        }

        try {
            $stmt = $db->prepare("INSERT INTO pinned_users (user_id, pinned_user_id) VALUES (?, ?)");
            $stmt->execute([$currentUser['user_id'], $userId]);
            return true;
        } catch (\PDOException $e) {
            // Якщо вже запінено, повертаємо true
            return true;
        }
    }

    public static function unpinUser($userId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("DELETE FROM pinned_users WHERE user_id = ? AND pinned_user_id = ?");
        $stmt->execute([$currentUser['user_id'], $userId]);
        
        return true;
    }

    public static function getBlockedUsers()
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("
            SELECT u.id, u.login, u.created_at 
            FROM users u
            INNER JOIN blocked_users bu ON u.id = bu.blocked_user_id
            WHERE bu.user_id = ?
        ");
        $stmt->execute([$currentUser['user_id']]);
        return $stmt->fetchAll();
    }

    public static function blockUser($userId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        if ($userId == $currentUser['user_id']) {
            throw new \Exception("Не можна заблокувати самого себе");
        }

        try {
            $stmt = $db->prepare("INSERT INTO blocked_users (user_id, blocked_user_id) VALUES (?, ?)");
            $stmt->execute([$currentUser['user_id'], $userId]);
            
            // Видаляємо з запінених якщо був запінений
            $stmt = $db->prepare("DELETE FROM pinned_users WHERE user_id = ? AND pinned_user_id = ?");
            $stmt->execute([$currentUser['user_id'], $userId]);
            
            return true;
        } catch (\PDOException $e) {
            // Якщо вже заблоковано, повертаємо true
            return true;
        }
    }

    public static function unblockUser($userId)
    {
        $currentUser = Auth::requireAuth();
        $db = self::getDb();
        
        $stmt = $db->prepare("DELETE FROM blocked_users WHERE user_id = ? AND blocked_user_id = ?");
        $stmt->execute([$currentUser['user_id'], $userId]);
        
        return true;
    }
}
