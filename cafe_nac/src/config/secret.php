<?php
/**
 * NAC Cafe - Internal Configuration
 * CONFIDENTIAL - Do not expose
 */

// Internal API Key
$api_key = "nac_internal_api_2024";

// Internal monitoring service
$internal_service_url = "http://internal-service:5000";

// Encryption settings for financial backups
$encryption_method = "XOR";
$encryption_key = $api_key;

// Debug mode (should be disabled in production)
$debug_mode = false;

// File upload settings
$max_upload_size = 5242880; // 5MB
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

// Session secret
$session_secret = "nac_s3cr3t_k3y_2024";
