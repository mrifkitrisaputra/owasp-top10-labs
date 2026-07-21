<?php
/**
 * NAC Cafe - Logout
 */
require_once __DIR__ . '/includes/functions.php';

log_activity("Logout: " . ($_SESSION['username'] ?? 'unknown'));

session_destroy();
setcookie('nac_session', '', time() - 3600, '/');
setcookie('user_prefs', '', time() - 3600, '/');

header('Location: /');
exit;
