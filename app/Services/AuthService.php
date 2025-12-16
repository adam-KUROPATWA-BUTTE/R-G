<?php
namespace Services;

use PDO;
use Models\Database;

/**
 * Authentication Service
 * Handles user authentication, registration, and session management
 */
class AuthService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Start session if not already started
     */
    public function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Get current logged in user
     */
    public function getCurrentUser(): ?array
    {
        $this->ensureSession();
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $cols = ['id', 'email'];
        if ($this->tableHasColumn('users', 'role')) $cols[] = 'role';
        if ($this->tableHasColumn('users', 'first_name')) $cols[] = 'first_name';
        if ($this->tableHasColumn('users', 'last_name')) $cols[] = 'last_name';

        $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return null;
        }

        $user += ['role' => null, 'first_name' => null, 'last_name' => null];
        return $user;
    }

    /**
     * Login user with email and password
     */
    public function login(string $email, string $password): bool
    {
        $this->ensureSession();

        $cols = ['id', 'email'];
        $pwdCol = $this->tableHasColumn('users', 'password_hash') ? 'password_hash' : 
                  ($this->tableHasColumn('users', 'password') ? 'password' : null);
        
        if ($pwdCol === null) {
            return false;
        }
        
        $cols[] = $pwdCol;
        if ($this->tableHasColumn('users', 'role')) $cols[] = 'role';

        $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        
        if (!$u) {
            return false;
        }

        $hash = $u[$pwdCol] ?? '';
        if (!is_string($hash) || !password_verify($password, $hash)) {
            return false;
        }

        $_SESSION['user_id'] = (int)$u['id'];
        return true;
    }

    /**
     * Register a new user
     */
    public function register(string $email, string $password, string $first_name = '', string $last_name = ''): array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Email invalide'];
        }
        
        if (strlen($password) < 6) {
            return [false, 'Le mot de passe doit contenir au moins 6 caractères'];
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return [false, 'Un compte existe déjà avec cet email'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $cols = ['email'];
        $vals = ['?'];
        $params = [$email];

        if ($this->tableHasColumn('users', 'password_hash')) {
            $cols[] = 'password_hash'; $vals[] = '?'; $params[] = $hash;
        } elseif ($this->tableHasColumn('users', 'password')) {
            $cols[] = 'password'; $vals[] = '?'; $params[] = $hash;
        } else {
            return [false, "La table users n'a pas de colonne mot de passe"];
        }

        if ($this->tableHasColumn('users', 'role')) { 
            $cols[] = 'role'; $vals[] = '?'; $params[] = 'user'; 
        }
        if ($this->tableHasColumn('users', 'first_name')) { 
            $cols[] = 'first_name'; $vals[] = '?'; $params[] = $first_name; 
        }
        if ($this->tableHasColumn('users', 'last_name')) { 
            $cols[] = 'last_name'; $vals[] = '?'; $params[] = $last_name; 
        }
        if ($this->tableHasColumn('users', 'created_at')) { 
            $cols[] = 'created_at'; $vals[] = '?'; $params[] = date('Y-m-d H:i:s'); 
        }

        $sql = "INSERT INTO users (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        $ins = $this->db->prepare($sql);
        $ins->execute($params);

        return [true, 'Compte créé avec succès. Vous pouvez vous connecter.'];
    }

    /**
     * Logout current user
     */
    public function logout(): void
    {
        $this->ensureSession();
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]
            );
        }
        
        session_destroy();
    }

    /**
     * Check if user is logged in
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && ($user['role'] ?? '') === 'admin';
    }

    /**
     * Helper to check if table has column
     */
    private function tableHasColumn(string $table, string $column): bool
    {
        if (function_exists('table_has_column')) {
            return table_has_column($table, $column);
        }
        // Fallback: try to query and catch exception
        try {
            $stmt = $this->db->prepare("SELECT $column FROM $table LIMIT 0");
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
}
