<?php
// Temporary debug helper -- run locally, delete after debugging
header('Content-Type: text/plain; charset=utf-8');
session_start();

echo "SESSION DEBUG\n";
echo "URL: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '') . "\n";
echo "PHPSESSID cookie (from _COOKIE): " . (isset($_COOKIE[session_name()]) ? $_COOKIE[session_name()] : '(none)') . "\n";
echo "session_id(): " . session_id() . "\n\n";

echo "\
");
print_r($_SESSION);

echo "\n\nOpen your login page at /blog_project/auth/login.php in another tab, then return here and reload to verify that the same session id and CSRF token are present.\n";

echo "If the PHPSESSID cookie is missing or session id changes between pages, the CSRF check will fail and login won't work.\n";

echo "After debugging, delete this file.\n";
?>