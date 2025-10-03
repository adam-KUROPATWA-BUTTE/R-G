<?php

namespace App\Models;

use App\Core\Model;

/**
 * User Model
 */
class User extends Model {
    protected $table = 'users';
    protected $fillable = [
        'email', 'password_hash', 'role', 'name', 
        'first_name', 'last_name', 'created_at'
    ];
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array {
        return $this->first(['email' => $email]);
    }
    
    /**
     * Create new user
     */
    public function createUser(string $email, string $password, array $data = []): int {
        $userData = [
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'user',
            'name' => $data['name'] ?? '',
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($userData);
    }
    
    /**
     * Verify user password
     */
    public function verifyPassword(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return null;
        }
        
        $hash = $user['password_hash'] ?? '';
        if (password_verify($password, $hash)) {
            return $user;
        }
        
        return null;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool {
        return $this->findByEmail($email) !== null;
    }
    
    /**
     * Get admin users
     */
    public function getAdmins(): array {
        return $this->where(['role' => 'admin']);
    }
    
    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
}
