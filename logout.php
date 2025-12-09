<?php
// Start session if it isn't already
if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

// Unset all session variables
$_SESSION = array();
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	// Delete session cookie by setting its expiration in the past
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destroy the session data on the server
session_destroy();

// Tell browsers not to cache logout page
header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

// Redirect to login and stop execution. Use absolute path where possible.
header('Location: login.php');
exit();
?>
