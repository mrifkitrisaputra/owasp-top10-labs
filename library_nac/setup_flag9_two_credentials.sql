-- ============================================
-- FLAG 9 SETUP - IDOR Challenge (HARD)
-- ============================================
-- Player memiliki 2 kredensial:
-- 1. Regular User: john_doe / password (tidak bisa akses admin panel)
-- 2. Librarian: librarian_admin / admin (bisa akses admin panel)
--
-- Untuk FLAG 9, player harus login sebagai librarian_admin

-- 1. Cek users yang sudah ada
SELECT '=============================' AS '';
SELECT 'EXISTING USERS CHECK' AS '';
SELECT '=============================' AS '';
SELECT id, username, role, is_active FROM users 
WHERE username IN ('john_doe', 'librarian_admin', 'admin', 'superadmin');

-- 2. Tambahkan user superadmin (yang punya FLAG)
INSERT INTO users (username, password, email, role, is_active, created_at) 
SELECT 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@library.com', 'admin', 1, '2024-01-10 08:00:00'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'superadmin');
-- Password: password

-- Tambahkan secondary_admin untuk variasi
INSERT INTO users (username, password, email, role, is_active, created_at) 
SELECT 'secondary_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secondary@library.com', 'librarian', 1, '2024-01-08 09:00:00'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'secondary_admin');
-- Password: password

-- 3. OPTIONAL: Hapus logs lama jika ingin reset
-- Uncomment baris di bawah jika ingin mulai dari awal
-- DELETE FROM admin_logs;

-- 4. Get user IDs
SET @john_doe_id = (SELECT id FROM users WHERE username = 'john_doe' LIMIT 1);
SET @librarian_admin_id = (SELECT id FROM users WHERE username = 'librarian_admin' LIMIT 1);
SET @superadmin_id = (SELECT id FROM users WHERE username = 'superadmin' LIMIT 1);
SET @secondary_admin_id = (SELECT id FROM users WHERE username = 'secondary_admin' LIMIT 1);

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'USER IDs FOR FLAG 9' AS '';
SELECT '=============================' AS '';
SELECT 
    username, 
    id, 
    role,
    CASE 
        WHEN username = 'john_doe' THEN 'Regular user - Cannot access admin panel'
        WHEN username = 'librarian_admin' THEN 'Player uses this to solve FLAG 9'
        WHEN username = 'superadmin' THEN 'Contains FLAG 9 in logs'
        WHEN username = 'secondary_admin' THEN 'Decoy admin'
    END as note
FROM users 
WHERE username IN ('john_doe', 'librarian_admin', 'superadmin', 'secondary_admin');

-- 5. Insert logs untuk librarian_admin (VISIBLE saat player login)
-- Player akan melihat logs ini saat login sebagai librarian_admin
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@librarian_admin_id, 'LOGIN', 'Librarian admin logged in successfully', '192.168.1.100', '2024-01-20 09:15:00'),
(@librarian_admin_id, 'VIEW_STATS', 'Viewed dashboard statistics', '192.168.1.100', '2024-01-20 09:16:30'),
(@librarian_admin_id, 'VIEW_BOOKS', 'Accessed book catalog management', '192.168.1.100', '2024-01-20 10:30:00'),
(@librarian_admin_id, 'UPDATE_BOOK', 'Updated book information: ID 42', '192.168.1.100', '2024-01-20 11:45:00'),
(@librarian_admin_id, 'ARCHIVE_BOOK', 'Archived damaged book: The Great Gatsby', '192.168.1.100', '2024-01-20 13:20:00'),
(@librarian_admin_id, 'MEMBER_MANAGEMENT', 'Reviewed member registrations', '192.168.1.100', '2024-01-20 14:00:00'),
(@librarian_admin_id, 'LOGOUT', 'Librarian admin logged out', '192.168.1.100', '2024-01-20 15:00:00'),
(@librarian_admin_id, 'LOGIN', 'Librarian admin logged in', '192.168.1.100', '2024-01-21 08:30:00'),
(@librarian_admin_id, 'REPORT_GENERATION', 'Generated weekly borrowing report', '192.168.1.100', '2024-01-21 10:15:00'),
(@librarian_admin_id, 'INVENTORY_CHECK', 'Performed inventory verification', '192.168.1.100', '2024-01-21 14:00:00'),
(@librarian_admin_id, 'LOGIN', 'Librarian admin logged in', '192.168.1.100', '2024-01-22 09:00:00');

-- 6. Insert logs untuk secondary_admin (HIDDEN dari librarian_admin)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@secondary_admin_id, 'LOGIN', 'Secondary admin logged in', '10.0.2.50', '2024-01-18 08:00:00'),
(@secondary_admin_id, 'USER_MANAGEMENT', 'Modified user permissions', '10.0.2.50', '2024-01-18 10:30:00'),
(@secondary_admin_id, 'BOOK_MAINTENANCE', 'Updated book catalog metadata', '10.0.2.50', '2024-01-18 14:00:00'),
(@secondary_admin_id, 'SYSTEM_CHECK', 'Ran system health diagnostics', '10.0.2.50', '2024-01-18 15:30:00'),
(@secondary_admin_id, 'LOGOUT', 'Secondary admin logged out', '10.0.2.50', '2024-01-18 16:00:00'),
(@secondary_admin_id, 'LOGIN', 'Secondary admin logged in', '10.0.2.50', '2024-01-19 07:45:00'),
(@secondary_admin_id, 'DATABASE_CLEANUP', 'Performed database maintenance', '10.0.2.50', '2024-01-19 11:00:00'),
(@secondary_admin_id, 'BACKUP_VERIFY', 'Verified backup integrity', '10.0.2.50', '2024-01-19 13:00:00'),
(@secondary_admin_id, 'LOGOUT', 'Secondary admin logged out', '10.0.2.50', '2024-01-19 17:00:00');

-- 7. Insert logs untuk superadmin (HIDDEN - CONTAINS FLAG 9)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@superadmin_id, 'LOGIN', 'Superadmin logged in', '203.0.113.42', '2024-01-14 13:00:00'),
(@superadmin_id, 'SYSTEM_CONFIG', 'Modified system security settings', '203.0.113.42', '2024-01-14 14:15:00'),
(@superadmin_id, 'DATABASE_BACKUP', 'Initiated full database backup', '203.0.113.42', '2024-01-14 16:00:00'),
(@superadmin_id, 'SECURITY_AUDIT', 'Performed routine security audit', '203.0.113.42', '2024-01-14 18:30:00'),
-- ⚠️⚠️⚠️ FLAG 9 ANSWER IS HERE ⚠️⚠️⚠️
-- Question: What IP did admin use on 2024-01-15 at 14:30?
-- Answer: 198.51.100.77
(@superadmin_id, 'CRITICAL_ACCESS', 'Accessed sensitive system files from remote location', '198.51.100.77', '2024-01-15 14:30:00'),
(@superadmin_id, 'USER_PRIVILEGE', 'Modified user privilege levels', '203.0.113.42', '2024-01-15 15:00:00'),
(@superadmin_id, 'AUDIT_REVIEW', 'Reviewed security logs and access patterns', '203.0.113.42', '2024-01-16 09:00:00'),
(@superadmin_id, 'POLICY_UPDATE', 'Updated library borrowing policies', '203.0.113.42', '2024-01-17 10:00:00'),
(@superadmin_id, 'FIREWALL_CONFIG', 'Modified firewall rules', '203.0.113.42', '2024-01-17 14:00:00');

-- 8. Additional noise logs
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@librarian_admin_id, 'VIEW_LOGS', 'Accessed admin logs page', '192.168.1.100', '2024-01-22 09:05:00'),
(@secondary_admin_id, 'LOGIN', 'Secondary admin logged in', '10.0.2.50', '2024-01-20 08:00:00'),
(@librarian_admin_id, 'USER_MANAGEMENT', 'Reviewed user accounts', '192.168.1.100', '2024-01-22 10:30:00');

-- ============================================
-- VERIFICATION SECTION
-- ============================================

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'VERIFICATION RESULTS' AS '';
SELECT '=============================' AS '';

-- Total logs
SELECT '' AS '';
SELECT 'Total logs in system:' AS info, COUNT(*) AS count FROM admin_logs;

-- Logs per user
SELECT '' AS '';
SELECT 'Logs breakdown by user:' AS info;
SELECT 
    u.username,
    u.role,
    COUNT(al.id) as log_count,
    CASE 
        WHEN u.username = 'librarian_admin' THEN 'VISIBLE to player'
        ELSE 'HIDDEN from player'
    END as visibility
FROM users u
LEFT JOIN admin_logs al ON u.id = al.admin_id
WHERE u.username IN ('librarian_admin', 'superadmin', 'secondary_admin')
GROUP BY u.username, u.role;

-- Logs visible to player (librarian_admin)
SELECT '' AS '';
SELECT 'Logs VISIBLE to player (librarian_admin):' AS info;
SELECT id, action, ip_address, DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:%s') as log_date
FROM admin_logs 
WHERE admin_id = @librarian_admin_id 
ORDER BY log_date DESC;

-- Logs hidden from player (superadmin)
SELECT '' AS '';
SELECT 'Logs HIDDEN from player (superadmin - contains FLAG):' AS info;
SELECT id, action, ip_address, DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:%s') as log_date
FROM admin_logs 
WHERE admin_id = @superadmin_id 
ORDER BY log_date DESC;

-- Logs hidden from player (secondary_admin)
SELECT '' AS '';
SELECT 'Logs HIDDEN from player (secondary_admin):' AS info;
SELECT id, action, ip_address, DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:%s') as log_date
FROM admin_logs 
WHERE admin_id = @secondary_admin_id 
ORDER BY log_date DESC;

-- FLAG 9 Location
SELECT '' AS '';
SELECT '=============================' AS '';
SELECT '🎯 FLAG 9 LOCATION' AS '';
SELECT '=============================' AS '';
SELECT 
    id AS log_id, 
    admin_id,
    (SELECT username FROM users WHERE id = admin_id) as admin_username,
    action, 
    details,
    ip_address as FLAG_ANSWER,
    DATE_FORMAT(log_date, '%Y-%m-%d %H:%i:%s') as log_date
FROM admin_logs
WHERE log_date = '2024-01-15 14:30:00';

-- Summary
SELECT '' AS '';
SELECT '=============================' AS '';
SELECT 'CTF CHALLENGE SUMMARY' AS '';
SELECT '=============================' AS '';
SELECT 'Challenge: FLAG 9 - IDOR Vulnerability' AS info;
SELECT 'Difficulty: HARD' AS info;
SELECT '' AS '';
SELECT '--- PLAYER CREDENTIALS ---' AS '';
SELECT 'Option 1 (Regular User): john_doe / password' AS credentials;
SELECT '  → Cannot access admin panel' AS note;
SELECT '  → Used for other flags/challenges' AS note;
SELECT '' AS '';
SELECT 'Option 2 (Librarian): librarian_admin / admin' AS credentials;
SELECT '  → CAN access admin panel' AS note;
SELECT '  → USE THIS to solve FLAG 9' AS note;
SELECT '' AS '';
SELECT '--- CHALLENGE INFO ---' AS '';
SELECT CONCAT('Player will see: ', COUNT(*), ' logs') AS visible_logs
FROM admin_logs WHERE admin_id = @librarian_admin_id;
SELECT CONCAT('Total in system: ', COUNT(*), ' logs') AS total_logs
FROM admin_logs;
SELECT 'Player must use IDOR to access hidden logs' AS exploit_method;
SELECT '' AS '';
SELECT '--- FLAG 9 ---' AS '';
SELECT 'Question: What IP did admin use on 2024-01-15 at 14:30?' AS question;
SELECT 'Answer: 198.51.100.77' AS flag_answer;
SELECT 'Location: Hidden in superadmin logs (must use IDOR)' AS location;

SELECT '' AS '';
SELECT '=============================' AS '';
SELECT '✅ SETUP COMPLETE!' AS '';
SELECT '=============================' AS '';
