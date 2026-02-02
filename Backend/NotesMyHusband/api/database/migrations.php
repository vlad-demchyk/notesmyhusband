<?php

namespace App\Database;

require_once __DIR__ . '/db.php';

use App\Database\Database;

class Migrations
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function run()
    {
        $this->createUsersTable();
        $this->createNotesTable();
        $this->createNoteRecipientsTable();
        $this->createBlockedUsersTable();
        $this->createPinnedUsersTable();
        
        echo "Міграції успішно виконано!\n";
    }

    private function createUsersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            login TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";

        $this->db->exec($sql);
    }

    private function createNotesTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS notes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            author_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        $this->db->exec($sql);
        
        // Індекс для швидкого пошуку нотаток автора
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_notes_author ON notes(author_id)");
    }

    private function createNoteRecipientsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS note_recipients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            note_id INTEGER NOT NULL,
            recipient_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE,
            FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(note_id, recipient_id)
        )";

        $this->db->exec($sql);
        
        // Індекси для швидкого пошуку
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_note_recipients_note ON note_recipients(note_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_note_recipients_recipient ON note_recipients(recipient_id)");
    }

    private function createBlockedUsersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS blocked_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            blocked_user_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(user_id, blocked_user_id)
        )";

        $this->db->exec($sql);
        
        // Індекс для швидкого пошуку заблокованих користувачів
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_blocked_users_user ON blocked_users(user_id)");
    }

    private function createPinnedUsersTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS pinned_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            pinned_user_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (pinned_user_id) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE(user_id, pinned_user_id)
        )";

        $this->db->exec($sql);
        
        // Індекс для швидкого пошуку запінених користувачів
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pinned_users_user ON pinned_users(user_id)");
    }
}

// Запуск міграцій якщо файл виконується напряму
if (php_sapi_name() === 'cli') {
    $migrations = new Migrations();
    $migrations->run();
}
