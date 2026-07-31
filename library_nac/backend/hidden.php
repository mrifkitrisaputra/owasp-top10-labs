<?php
// FLAG 3: This page is accessible only with valid_user cookie set to "true"
// The flag answer for FLAG 3 is: true

require_once 'config.php';
require_once 'auth.php';

if (!isset($_COOKIE['valid_user']) || $_COOKIE['valid_user'] !== 'true') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Valid user cookie required.']);
    exit();
}

// FLAG 3 verification content
echo json_encode([
    'success' => true,
    'message' => 'Congratulations! You found the hidden area.',
    'hint' => 'The cookie value that granted you access is the answer to FLAG 3.'
]);
?>
