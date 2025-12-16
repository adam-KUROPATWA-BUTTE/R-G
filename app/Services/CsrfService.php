<?php
namespace Services;

/**
 * CSRF Protection Service
 * Handles CSRF token generation and validation
 */
class CsrfService
{
    /**
     * Ensure session is started
     */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Generate or retrieve CSRF token
     */
    public function getToken(): string
    {
        $this->ensureSession();
        
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verify(?string $token, bool $rotateOnSuccess = false): bool
    {
        $this->ensureSession();
        
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $ok = is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
        
        if ($ok && $rotateOnSuccess) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $ok;
    }

    /**
     * Generate CSRF input field HTML
     */
    public function getInputField(): string
    {
        $token = htmlspecialchars($this->getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf" value="' . $token . '">';
    }

    /**
     * Validate CSRF token from POST data
     * Throws exception on invalid token
     */
    public function validate(): void
    {
        if (!$this->verify($_POST['csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF token invalide');
        }
    }
}
