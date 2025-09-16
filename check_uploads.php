<?php
$abs = __DIR__ . '/uploads';
echo "<pre>";
echo "abs: $abs\n";
echo "exists: " . (is_dir($abs) ? 'yes' : 'no') . "\n";
echo "writable: " . (is_writable($abs) ? 'yes' : 'no') . "\n";
$sample = $abs . '/.keep';
@file_put_contents($sample, "ok");
echo "write test: " . (file_exists($sample) ? 'ok' : 'fail') . "\n";
echo "web url should be: " . rtrim(dirname($_SERVER['SCRIPT_NAME']),'/') . "/uploads/.keep\n";
echo "</pre>";