-- ============================================
-- FLAG 9 SETUP - IDOR Challenge (HARD)
-- ============================================
-- Script ini akan menambahkan data untuk FLAG 9
-- tanpa mengganggu data existing

-- 1. Tambahkan user superadmin dan head_librarian
-- Cek dulu apakah sudah ada
INSERT INTO users (username, password, email, role, is_active, created_at) 
SELECT 'superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@library.com', 'admin', 1, '2024-01-10 08:00:00'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'superadmin');

INSERT INTO users (username, password, email, role, is_active, created_at) 
SELECT 'head_librarian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian@library.com', 'librarian', 1, '2024-01-05 09:00:00'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'head_librarian');

-- Password untuk semua: password

-- 2. Hapus logs lama jika ada (opsional, uncomment jika ingin reset)
-- DELETE FROM admin_logs;

-- 3. Dapatkan ID users
SET @admin_id = (SELECT id FROM users WHERE username = 'admin' LIMIT 1);
SET @superadmin_id = (SELECT id FROM users WHERE username = 'superadmin' LIMIT 1);
SET @librarian_id = (SELECT id FROM users WHERE username = 'head_librarian' LIMIT 1);

-- Tampilkan ID untuk verifikasi
SELECT 'User IDs:' AS info;
SELECT username, id, role FROM users WHERE username IN ('admin', 'superadmin', 'head_librarian');

-- 4. Insert logs untuk admin biasa (yang akan visible saat player login)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@admin_id, 'LOGIN', 'Admin logged in successfully', '192.168.1.100', '2024-01-20 09:15:00'),
(@admin_id, 'VIEW_STATS', 'Viewed dashboard statistics', '192.168.1.100', '2024-01-20 09:16:30'),
(@admin_id, 'VIEW_USERS', 'Accessed user management page', '192.168.1.100', '2024-01-20 10:30:00'),
(@admin_id, 'UPDATE_BOOK', 'Updated book information: ID 42', '192.168.1.100', '2024-01-20 11:45:00'),
(@admin_id, 'LOGOUT', 'Admin logged out', '192.168.1.100', '2024-01-20 12:00:00');

-- 5. Insert logs untuk head librarian (hidden dari admin biasa)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@librarian_id, 'LOGIN', 'Librarian logged in', '10.0.2.50', '2024-01-18 08:00:00'),
(@librarian_id, 'ARCHIVE_BOOK', 'Archived book: War and Peace', '10.0.2.50', '2024-01-18 10:30:00'),
(@librarian_id, 'LOGOUT', 'Librarian logged out', '10.0.2.50', '2024-01-18 16:00:00'),
(@librarian_id, 'LOGIN', 'Librarian logged in', '10.0.2.50', '2024-01-19 07:45:00'),
(@librarian_id, 'BOOK_MAINTENANCE', 'Updated book catalog', '10.0.2.50', '2024-01-19 11:00:00');

-- 6. Insert logs untuk superadmin (BERISI FLAG 9)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@superadmin_id, 'LOGIN', 'Superadmin logged in', '203.0.113.42', '2024-01-14 13:00:00'),
(@superadmin_id, 'SYSTEM_CONFIG', 'Modified system security settings', '203.0.113.42', '2024-01-14 14:15:00'),
(@superadmin_id, 'DATABASE_BACKUP', 'Initiated full database backup', '203.0.113.42', '2024-01-14 16:00:00'),
(@superadmin_id, 'SECURITY_AUDIT', 'Performed routine security audit', '203.0.113.42', '2024-01-14 18:30:00'),
-- ⚠️ FLAG 9 ADA DI BARIS INI ⚠️
(@superadmin_id, 'CRITICAL_ACCESS', 'Accessed sensitive system files from remote location', '198.51.100.77', '2024-01-15 14:30:00'),
(@superadmin_id, 'USER_PRIVILEGE', 'Modified user privilege levels', '203.0.113.42', '2024-01-15 15:00:00'),
(@superadmin_id, 'AUDIT_REVIEW', 'Reviewed security logs', '203.0.113.42', '2024-01-16 09:00:00'),
(@superadmin_id, 'POLICY_UPDATE', 'Updated borrowing policies', '203.0.113.42', '2024-01-17 10:00:00');

-- 7. Tambahan logs untuk admin biasa (recent activity)
INSERT INTO admin_logs (admin_id, action, details, ip_address, log_date) VALUES
(@admin_id, 'LOGIN', 'Admin logged in', '192.168.1.100', '2024-01-19 08:30:00'),
(@admin_id, 'REPORT_GENERATION', 'Generated monthly report', '192.168.1.100', '2024-01-21 14:00:00'),
(@admin_id, 'LOGIN', 'Admin logged in', '192.168.1.100', '2024-01-22 09:00:00');

-- 8. Verifikasi data
SELECT '
=== VERIFICATION ===' AS info;
SELECT 'Total logs in system:' AS info, COUNT(*) AS count FROM admin_logs;

SELECT '
Admin logs (should be visible):' AS info;
SELECT id, action, ip_address, log_date 
FROM admin_logs 
WHERE admin_id = @admin_id 
ORDER BY log_date DESC;

SELECT '
Superadmin logs (should be hidden, contains FLAG):' AS info;
SELECT id, action, ip_address, log_date 
FROM admin_logs 
WHERE admin_id = @superadmin_id 
ORDER BY log_date DESC;

SELECT '
FLAG 9 Location:' AS info;
SELECT id AS log_id, admin_id, action, ip_address, log_date
FROM admin_logs
WHERE log_date = '2024-01-15 14:30:00';

SELECT '
=== SETUP COMPLETE ===' AS info;
SELECT 'Players should login as: admin / password' AS instruction;
SELECT 'FLAG 9 Answer: 198.51.100.77' AS flag_answer;
SELECT 'Question: What IP did admin use on 2024-01-15 at 14:30?' AS question;
