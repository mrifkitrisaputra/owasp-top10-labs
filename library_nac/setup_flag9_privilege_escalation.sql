-- ============================================
-- FLAG 9 SETUP - Privilege Escalation
-- ============================================
-- Challenge: Hidden Configuration Access
-- Question: What is the master_key value in the system configuration?

-- 1. Create system_config table (if not exists)
CREATE TABLE IF NOT EXISTS system_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(255) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    is_hidden TINYINT(1) DEFAULT 0,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Clear existing config (optional - uncomment if needed)
-- DELETE FROM system_config;

-- 3. Insert visible (non-hidden) configs
INSERT INTO system_config (config_key, config_value, is_hidden, description) VALUES
('site_name', 'Digital Library System', 0, 'Name of the library system'),
('max_borrow_days', '14', 0, 'Maximum days a book can be borrowed'),
('max_books_per_user', '5', 0, 'Maximum number of books per user'),
('late_fee_per_day', '1000', 0, 'Late fee in Rupiah per day'),
('enable_notifications', 'true', 0, 'Enable email notifications'),
('maintenance_mode', 'false', 0, 'System maintenance mode'),
('session_timeout', '3600', 0, 'Session timeout in seconds')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    is_hidden = VALUES(is_hidden),
    description = VALUES(description);

-- 4. Insert HIDDEN configs (FLAG 9 is here!)
INSERT INTO system_config (config_key, config_value, is_hidden, description) VALUES
-- ⚠️⚠️⚠️ FLAG 9 ANSWER IS HERE ⚠️⚠️⚠️
('master_key', 'MASTER-KEY-2024-LIBRARY-NAC', 1, 'Master encryption key - CONFIDENTIAL'),
('api_secret', 'sk_live_a8f7d9e2c4b1f3e6a9d2c5b8e1f4a7d0', 1, 'API secret key for external integrations'),
('database_password', 'S3cur3_P@ssw0rd_2024!', 1, 'Database root password'),
('admin_recovery_code', 'RECOVERY-2024-ADMIN-XK9P', 1, 'Emergency admin recovery code'),
('encryption_salt', 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6', 1, 'Salt for password encryption')
ON DUPLICATE KEY UPDATE 
    config_value = VALUES(config_value),
    is_hidden = VALUES(is_hidden),
    description = VALUES(description);

-- 5. Verification
SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'FLAG 9 - SYSTEM CONFIG SETUP' AS '';
SELECT '=============================' AS '';

SELECT '' AS '';
SELECT 'Total configurations:' AS info, COUNT(*) AS count FROM system_config;

SELECT '' AS '';
SELECT 'Visible configurations (is_hidden = 0):' AS info;
SELECT config_key, config_value, description 
FROM system_config 
WHERE is_hidden = 0
ORDER BY config_key;

SELECT '' AS '';
SELECT 'Hidden configurations (is_hidden = 1):' AS info;
SELECT config_key, config_value, description 
FROM system_config 
WHERE is_hidden = 1
ORDER BY config_key;

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT '🎯 FLAG 9 LOCATION' AS '';
SELECT '=============================' AS '';
SELECT 
    id,
    config_key,
    config_value AS FLAG_ANSWER,
    is_hidden,
    description
FROM system_config
WHERE config_key = 'master_key';

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'CHALLENGE SUMMARY' AS '';
SELECT '=============================' AS '';
SELECT 'FLAG 9: Privilege Escalation - Hidden Configuration' AS challenge;
SELECT 'Difficulty: Very Hard' AS level;
SELECT '' AS '';
SELECT 'Login as: librarian_admin / admin' AS credentials;
SELECT 'Navigate to: Admin Panel → System Config tab' AS step1;
SELECT 'By default: Only sees 7 visible configs' AS step2;
SELECT 'Discover: Checkbox "Show hidden configurations"' AS step3;
SELECT 'Enable checkbox: Reveals 5 hidden configs (Privilege Escalation!)' AS step4;
SELECT 'Find: master_key in hidden configs' AS step5;
SELECT '' AS '';
SELECT 'Question: What is the master_key value in the system configuration?' AS question;
SELECT 'Answer: CTF{H1dd3n_C0nf1g_Pr1v_3sc4l4t10n}' AS flag_answer;

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT '✅ FLAG 9 SETUP COMPLETE!' AS '';
SELECT '=============================' AS '';

-- 6. Security Note (for educational purposes)
SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'VULNERABILITY EXPLANATION' AS '';
SELECT '=============================' AS '';
SELECT 'The backend checks if user isAdmin() but does NOT check privilege level' AS vuln1;
SELECT 'librarian_admin (lower privilege) can access configs meant for superadmin only' AS vuln2;
SELECT 'Parameter show_hidden=true reveals sensitive configuration data' AS vuln3;
SELECT '' AS '';
SELECT 'Proper Fix: Check user role explicitly' AS fix;
SELECT 'if ($_SESSION[role] !== superadmin) { deny access to hidden configs }' AS fix_code;
