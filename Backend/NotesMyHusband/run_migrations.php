<?php
$db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, login VARCHAR(255) NOT NULL UNIQUE, email VARCHAR(255) NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL)");
$db->exec("CREATE TABLE IF NOT EXISTS notes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, author_id INTEGER NOT NULL, content TEXT NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS note_recipients (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, note_id INTEGER NOT NULL, recipient_id INTEGER NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, UNIQUE(note_id, recipient_id), FOREIGN KEY (note_id) REFERENCES notes(id) ON DELETE CASCADE, FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS blocked_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, blocked_user_id INTEGER NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, UNIQUE(user_id, blocked_user_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS pinned_users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, pinned_user_id INTEGER NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, UNIQUE(user_id, pinned_user_id), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (pinned_user_id) REFERENCES users(id) ON DELETE CASCADE)");
$db->exec("CREATE TABLE IF NOT EXISTS personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, tokenable_type VARCHAR(255) NOT NULL, tokenable_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, token VARCHAR(64) NOT NULL UNIQUE, abilities TEXT NULL, last_used_at DATETIME NULL, expires_at DATETIME NULL, created_at DATETIME NULL, updated_at DATETIME NULL)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_notes_author_id ON notes(author_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_note_recipients_note_id ON note_recipients(note_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_note_recipients_recipient_id ON note_recipients(recipient_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_blocked_users_user_id ON blocked_users(user_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_pinned_users_user_id ON pinned_users(user_id)");
$db->exec("CREATE INDEX IF NOT EXISTS idx_personal_access_tokens_tokenable ON personal_access_tokens(tokenable_type, tokenable_id)");

echo "Migrations completed.\n";
