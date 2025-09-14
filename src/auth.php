<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/schema.php';

function session_boot(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        $cfg = (function_exists('config') ? (config()['app'] ?? []) : []);
        if (!empty($cfg['session_name'])) {
            session_name($cfg['session_name']);
        }
        session_start();
    }
}

function current_user(): ?array {
    session_boot();
    if (empty($_SESSION['user_id'])) return null;

    $cols = ['id', 'email']; // colonnes minimales
    if (table_has_column('users', 'role')) $cols[] = 'role';
    if (table_has_column('users', 'first_name')) $cols[] = 'first_name';
    if (table_has_column('users', 'last_name')) $cols[] = 'last_name';

    $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE id = ? LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) return null;

    $user += ['role' => null, 'first_name' => null, 'last_name' => null];
    return $user;
}

function login_user(string $email, string $password): bool {
    session_boot();

    $cols = ['id', 'email'];
    $pwdCol = table_has_column('users', 'password_hash') ? 'password_hash' : (table_has_column('users', 'password') ? 'password' : null);
    if ($pwdCol === null) {
        return false;
    }
    $cols[] = $pwdCol;
    if (table_has_column('users', 'role')) $cols[] = 'role';

    $sql = "SELECT " . implode(',', $cols) . " FROM users WHERE email = ? LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if (!$u) return false;

    $hash = $u[$pwdCol] ?? '';
    if (!is_string($hash) || !password_verify($password, $hash)) {
        return false;
    }

    $_SESSION['user_id'] = (int)$u['id'];
    return true;
}

function register_user(string $email, string $password, string $first_name = '', string $last_name = ''): array {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Email invalide'];
    }
    if (strlen($password) < 6) {
        return [false, 'Le mot de passe doit contenir au moins 6 caractères'];
    }

    $stmt = db()->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return [false, 'Un compte existe déjà avec cet email'];
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $cols = ['email'];
    $vals = ['?'];
    $params = [$email];

    if (table_has_column('users', 'password_hash')) {
        $cols[] = 'password_hash'; $vals[] = '?'; $params[] = $hash;
    } elseif (table_has_column('users', 'password')) {
        $cols[] = 'password'; $vals[] = '?'; $params[] = $hash;
    } else {
        return [false, "La table users n'a pas de colonne mot de passe ('password_hash' ou 'password')"];
    }

    if (table_has_column('users', 'role')) { $cols[] = 'role'; $vals[] = '?'; $params[] = 'user'; }
    if (table_has_column('users', 'first_name')) { $cols[] = 'first_name'; $vals[] = '?'; $params[] = $first_name; }
    if (table_has_column('users', 'last_name')) { $cols[] = 'last_name'; $vals[] = '?'; $params[] = $last_name; }
    if (table_has_column('users', 'created_at')) { $cols[] = 'created_at'; $vals[] = '?'; $params[] = date('Y-m-d H:i:s'); }

    $sql = "INSERT INTO users (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
    $ins = db()->prepare($sql);
    $ins->execute($params);

    return [true, 'Compte créé avec succès. Vous pouvez vous connecter.'];
}

function logout_user(): void {
    session_boot();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

function require_login(): void {
    if (!current_user()) {
        header('Location: login.php');
        exit;
    }
}

function require_admin(): void {
    $u = current_user();
    if (!$u || (($u['role'] ?? '') !== 'admin')) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}