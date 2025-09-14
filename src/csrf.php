<?php
function csrf_start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Important: si session_boot (défini dans src/auth.php) existe, on l'utilise
        if (function_exists('session_boot')) {
            session_boot();
        } else {
            // Fallback si auth.php n'est pas chargé
            session_start();
        }
    }
}

function csrf_token(): string {
    csrf_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $t = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="'.$t.'">';
}

function csrf_validate(): void {
    csrf_start();
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        exit('CSRF token invalide.');
    }
}