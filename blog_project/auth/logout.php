<?php
// Destroy the session and redirect to home page
session_start();

// Unset all session variables
$_SESSION = [];

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'],
		$params['secure'], $params['httponly']
	);
}

// Finally destroy the session.
session_destroy();

// Redirect back to the homepage (or login page)
header('Location: ../index.php');
exit;
