<?php
namespace Models;

use PDO;

/**
 * User Model
 * Handles user authentication and management
 */
class User
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    /**
     * Get user by ID
     */
    public function getById(int $id): ?array
    {
        $cols = ['id', 'email'];
        if ($this->hasColumn('role')) $cols[] = 'role';
        if ($this->hasColumn('first_name')) $cols[] = 'first_name';
        if ($this->hasColumn('last_name')) $cols[] = 'last_name';
        if ($this->hasColumn('created_at')) $cols[] = 'created_at';

        $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return null;
        
        $user += ['role' => null, 'first_name' => null, 'last_name' => null];
        return $user;
    }

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?array
    {
        $cols = ['id', 'email'];
        $pwdCol = $this->getPasswordColumn();
        if ($pwdCol) $cols[] = $pwdCol;
        if ($this->hasColumn('role')) $cols[] = 'role';
        if ($this->hasColumn('first_name')) $cols[] = 'first_name';
        if ($this->hasColumn('last_name')) $cols[] = 'last_name';

        $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $user ?: null;
    }

    /**
     * Authenticate user
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->getByEmail($email);
        if (!$user) return null;

        $pwdCol = $this->getPasswordColumn();
        if (!$pwdCol) return null;

        $hash = $user[$pwdCol] ?? '';
        if (!is_string($hash) || !password_verify($password, $hash)) {
            return null;
        }

        return $user;
    }

    /**
     * Register new user
     */
    public function register(string $email, string $password, string $firstName = '', string $lastName = ''): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Invalid email'];
        }
        if (strlen($password) < 6) {
            return [false, 'Password must be at least 6 characters'];
        }

        // Check if email already exists
        if ($this->getByEmail($email)) {
            return [false, 'An account already exists with this email'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $cols = ['email'];
        $vals = ['?'];
        $params = [$email];

        $pwdCol = $this->getPasswordColumn();
        if (!$pwdCol) {
            return [false, "Users table doesn't have a password column"];
        }
        $cols[] = $pwdCol;
        $vals[] = '?';
        $params[] = $hash;

        if ($this->hasColumn('role')) {
            $cols[] = 'role';
            $vals[] = '?';
            $params[] = 'user';
        }
        if ($this->hasColumn('first_name')) {
            $cols[] = 'first_name';
            $vals[] = '?';
            $params[] = $firstName;
        }
        if ($this->hasColumn('last_name')) {
            $cols[] = 'last_name';
            $vals[] = '?';
            $params[] = $lastName;
        }
        if ($this->hasColumn('created_at')) {
            $cols[] = 'created_at';
            $vals[] = '?';
            $params[] = date('Y-m-d H:i:s');
        }

        $sql = "INSERT INTO users (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [true, 'Account created successfully'];
    }

    /**
     * Update user
     */
    public function update(int $id, array $data): void
    {
        $allowed = ['email', 'first_name', 'last_name', 'role'];
        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if ($this->hasColumn($field) && isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (isset($data['password']) && $data['password'] !== '') {
            $pwdCol = $this->getPasswordColumn();
            if ($pwdCol) {
                $fields[] = "$pwdCol = ?";
                $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
        }

        if (empty($fields)) return;

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Delete user
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Get all users
     */
    public function getAll(): array
    {
        $cols = ['id', 'email'];
        if ($this->hasColumn('role')) $cols[] = 'role';
        if ($this->hasColumn('first_name')) $cols[] = 'first_name';
        if ($this->hasColumn('last_name')) $cols[] = 'last_name';
        if ($this->hasColumn('created_at')) $cols[] = 'created_at';

        $sql = "SELECT " . implode(',', $cols) . " FROM users ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if column exists in users table
     */
    private function hasColumn(string $column): bool
    {
        if (function_exists('table_has_column')) {
            return table_has_column('users', $column);
        }
        
        // Fallback
        $stmt = $this->pdo->query("PRAGMA table_info(users)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['name'] === $column) return true;
        }
        return false;
    }

    /**
     * Get password column name
     */
    private function getPasswordColumn(): ?string
    {
        if ($this->hasColumn('password_hash')) return 'password_hash';
        if ($this->hasColumn('password')) return 'password';
        return null;
    }
}
