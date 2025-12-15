<?php
declare(strict_types=1);

function csrf_ensure_session(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function csrf_token(): string {
    csrf_ensure_session();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(?string $token, bool $rotateOnSuccess = false): bool {
    csrf_ensure_session();
    $sessionToken = $_SESSION['csrf_token'] ?? '';
    $ok = is_string($token) && is_string($sessionToken) && hash_equals($sessionToken, $token);
    if ($ok && $rotateOnSuccess) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $ok;
}

function csrf_input(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf" value="'.$t.'">';
}

// Alias de compat Laravel si des templates utilisent csrf_field()
if (!function_exists('csrf_field')) {
    function csrf_field(): string { return csrf_input(); }
}

// Alias for backward compatibility with existing code
if (!function_exists('csrf_validate')) {
    function csrf_validate(): void {
        if (!csrf_verify($_POST['csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF token invalide');
        }
    }
}