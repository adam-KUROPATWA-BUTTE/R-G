<?php
session_start();
require_once __DIR__ . '/src/auth.php';
require_admin();

echo "<h1>📋 Logs PHP (100 dernières lignes)</h1>";
echo "<p><a href='?refresh=1'>🔄 Rafraîchir</a> | <a href='?clear=1'>🗑️ Vider</a></p>";

// Trouver le fichier de log
$possibleLogs = [
    __DIR__ . '/error_log',
    __DIR__ . '/../error_log',
    ini_get('error_log'),
    '/var/log/php_errors.log'
];

$logFile = null;
foreach ($possibleLogs as $file) {
    if ($file && file_exists($file)) {
        $logFile = $file;
        break;
    }
}

if (isset($_GET['clear']) && $logFile) {
    file_put_contents($logFile, '');
    echo "<p style='color:green;'>✅ Logs vidés !</p>";
    echo "<meta http-equiv='refresh' content='1;url=view_logs.php'>";
    exit;
}

echo "<p>Fichier: <code>" . ($logFile ?? 'Introuvable') . "</code></p>";

if ($logFile && file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $lastLines = array_slice($lines, -100);
    
    echo "<pre style='background:#1e1e1e; color:#d4d4d4; padding:20px; overflow:auto; max-height:600px; border-radius:5px;'>";
    foreach ($lastLines as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'revolut') !== false) {
            echo "<span style='color:#ff6b6b;'>" . htmlspecialchars($line) . "</span>\n";
        } elseif (stripos($line, 'success') !== false) {
            echo "<span style='color:#51cf66;'>" . htmlspecialchars($line) . "</span>\n";
        } else {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ Impossible de trouver les logs PHP</p>";
    echo "<p>Essayez de regarder dans votre panel d'hébergement LWS → Logs</p>";
}
?><?php
session_start();
require_once __DIR__ . '/src/auth.php';
require_admin();

echo "<h1>📋 Logs PHP (100 dernières lignes)</h1>";
echo "<p><a href='?refresh=1'>🔄 Rafraîchir</a> | <a href='?clear=1'>🗑️ Vider</a></p>";

// Trouver le fichier de log
$possibleLogs = [
    __DIR__ . '/error_log',
    __DIR__ . '/../error_log',
    ini_get('error_log'),
    '/var/log/php_errors.log'
];

$logFile = null;
foreach ($possibleLogs as $file) {
    if ($file && file_exists($file)) {
        $logFile = $file;
        break;
    }
}

if (isset($_GET['clear']) && $logFile) {
    file_put_contents($logFile, '');
    echo "<p style='color:green;'>✅ Logs vidés !</p>";
    echo "<meta http-equiv='refresh' content='1;url=view_logs.php'>";
    exit;
}

echo "<p>Fichier: <code>" . ($logFile ?? 'Introuvable') . "</code></p>";

if ($logFile && file_exists($logFile)) {
    $logs = file_get_contents($logFile);
    $lines = explode("\n", $logs);
    $lastLines = array_slice($lines, -100);
    
    echo "<pre style='background:#1e1e1e; color:#d4d4d4; padding:20px; overflow:auto; max-height:600px; border-radius:5px;'>";
    foreach ($lastLines as $line) {
        if (stripos($line, 'error') !== false || stripos($line, 'revolut') !== false) {
            echo "<span style='color:#ff6b6b;'>" . htmlspecialchars($line) . "</span>\n";
        } elseif (stripos($line, 'success') !== false) {
            echo "<span style='color:#51cf66;'>" . htmlspecialchars($line) . "</span>\n";
        } else {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p style='color:red;'>❌ Impossible de trouver les logs PHP</p>";
    echo "<p>Essayez de regarder dans votre panel d'hébergement LWS → Logs</p>";
}
?>