<?php
/**
 * Nac News Portal - Logout
 */
require_once __DIR__ . '/includes/auth.php';
logoutUser();
header('Location: /login.php');
exit;
