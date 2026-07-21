# CTF Hints System

This file contains progressive hints for each flag. Release these hints gradually during the CTF to help participants who are stuck.

---

## FLAG 1: SQL Injection - ISBN of Oldest Mystery Book

### Hint 1 (Day 3):
The search functionality might not properly sanitize input. Try searching for unusual characters.

### Hint 2 (Day 5):
SQL injection is possible. Look for the oldest book in the Mystery category. The year matters.

### Hint 3 (Day 7):
Use UNION SELECT to query the books table directly. Filter by `category='Mystery'` and `ORDER BY publish_year ASC`.

---

## FLAG 2: Information Disclosure - Database Name

### Hint 1 (Day 2):
Sometimes developers leave important information in unexpected places. Check the source code of the web pages.

### Hint 2 (Day 4):
HTML comments can contain useful information. Look in the footer or included files.

### Hint 3 (Day 6):
View the source of index.html and search for "database" or "system".

---

## FLAG 3: Cookie Manipulation - Hidden Area Access

### Hint 1 (Day 7):
After logging in, check what cookies are set in your browser. What do they contain?

### Hint 2 (Day 9):
There's a hidden.php file in the backend. What cookie value would grant you access?

### Hint 3 (Day 11):
Set a cookie named `valid_user` with a boolean value to access restricted areas.

---

## FLAG 4: Session Analysis - Librarian Session Variable

### Hint 1 (Day 10):
Different user roles have different session variables set. What's special about librarian accounts?

### Hint 2 (Day 12):
After logging in as librarian, examine the authentication code to see what session variables are created.

### Hint 3 (Day 14):
Look for `admin_access` session variable in the auth.php file or API responses.

---

## FLAG 5: File Inclusion - Debug Parameter

### Hint 1 (Day 14):
Sometimes developers leave debug features enabled. Check if there are any debug parameters in the API.

### Hint 2 (Day 16):
Look for URL parameters that might include files. Try `debug` and `file` parameters in api.php.

### Hint 3 (Day 18):
Use `?debug=1&file=config.php` to include files. Test with different file paths.

---

## FLAG 6: Archive System - Storage Location

### Hint 1 (Day 18):
The archive system stores old books. Each archive has a code. Edgar Allan Poe's book has a code format: ARC-YEAR-AUTHOR.

### Hint 2 (Day 20):
The archive code is ARC-1841-POE. Search for it in the admin panel's archive section.

### Hint 3 (Day 22):
Use the archive search: `?action=archive&code=ARC-1841-POE` or query the archive_books table via SQL injection.

---

## FLAG 7: Authentication - Librarian Username

### Hint 1 (Day 21):
There's a special account with librarian privileges. Try to enumerate users in the database.

### Hint 2 (Day 23):
Use SQL injection to query the users table: `SELECT username FROM users WHERE role='librarian'`

### Hint 3 (Day 25):
The librarian username follows the pattern: librarian_admin. It's also mentioned on the login page.

---

## FLAG 8: Cryptography - Librarian Password

### Hint 1 (Day 25):
Extract the password hash for librarian_admin from the database. Is it the same hashing algorithm as other users?

### Hint 2 (Day 27):
The hash is SHA-256 (64 characters). Try common library-related passwords for 2024.

### Hint 3 (Day 29):
The password follows the pattern: library + year. Hash: 8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918

---

## FLAG 9: IDOR - Admin IP Address

### Hint 1 (Day 30):
Admin logs can be accessed by ID. Is there proper authorization checking for specific log entries?

### Hint 2 (Day 32):
Try accessing admin logs directly by ID: `?action=logs&log_id=1`. Look for logs from January 15, 2024 at 14:30.

### Hint 3 (Day 34):
The first admin log (ID 1) contains the information you need. Check the IP address field.

---

## FLAG 10: Privilege Escalation - Master Key

### Hint 1 (Day 35):
System configurations have a hidden flag. Some configs are marked as hidden and won't show by default.

### Hint 2 (Day 37):
In the admin panel's config section, there's a checkbox to show hidden configurations.

### Hint 3 (Day 39):
Use `?action=config&show_hidden=true` or check the checkbox in the UI. Look for `master_key`.

---

## FLAG 11: Complex Chain - User Email

### Hint 1 (Day 40):
This requires connecting multiple pieces of information: oldest mystery book → its author → who borrowed it → their email.

### Hint 2 (Day 42):
Step 1: Find oldest mystery book (FLAG 1)
Step 2: Check borrowings table for that book_id
Step 3: Find user_id from borrowings
Step 4: Get email from users table

### Hint 3 (Day 45):
Complex SQL query:
```sql
SELECT u.email FROM users u
JOIN borrowings b ON u.id = b.user_id  
JOIN books bk ON b.book_id = bk.id
WHERE bk.id = (SELECT id FROM books WHERE category='Mystery' ORDER BY publish_year ASC LIMIT 1)
LIMIT 1
```

---

## Hint Release Schedule

**Week 1-2 (Easy Flags)**
- Day 2: FLAG 2 Hint 1
- Day 3: FLAG 1 Hint 1
- Day 4: FLAG 2 Hint 2
- Day 5: FLAG 1 Hint 2

**Week 2-4 (Medium Flags)**
- Day 7: FLAG 1 Hint 3, FLAG 3 Hint 1
- Day 9: FLAG 3 Hint 2
- Day 10: FLAG 4 Hint 1
- Day 11: FLAG 3 Hint 3
- Day 12: FLAG 4 Hint 2
- Day 14: FLAG 4 Hint 3, FLAG 5 Hint 1

**Week 4-6 (Medium-Hard Flags)**
- Day 16: FLAG 5 Hint 2
- Day 18: FLAG 5 Hint 3, FLAG 6 Hint 1
- Day 20: FLAG 6 Hint 2
- Day 21: FLAG 7 Hint 1
- Day 22: FLAG 6 Hint 3

**Week 6-8 (Hard Flags)**
- Day 23: FLAG 7 Hint 2
- Day 25: FLAG 7 Hint 3, FLAG 8 Hint 1
- Day 27: FLAG 8 Hint 2
- Day 29: FLAG 8 Hint 3

**Week 8-12 (Very Hard Flags)**
- Day 30: FLAG 9 Hint 1
- Day 32: FLAG 9 Hint 2
- Day 34: FLAG 9 Hint 3
- Day 35: FLAG 10 Hint 1
- Day 37: FLAG 10 Hint 2
- Day 39: FLAG 10 Hint 3
- Day 40: FLAG 11 Hint 1
- Day 42: FLAG 11 Hint 2
- Day 45: FLAG 11 Hint 3

---

## Additional Support

### For Participants Completely Stuck:

**Week 3**: Offer 1-on-1 consultation for Flags 1-2
**Week 5**: Group workshop on SQL injection techniques
**Week 7**: Session on authentication vulnerabilities
**Week 9**: Advanced exploitation techniques workshop
**Week 11**: Final push - office hours for remaining flags

### Emergency Hints (Use sparingly):

If < 25% of teams have found a flag after expected timeframe, consider releasing additional hints or hosting a mini-workshop on that vulnerability type.

---

## Hint Delivery Methods

1. **Discord/Slack Bot**: Automated hint releases
2. **Email**: Scheduled emails to registered participants
3. **Website**: Update a hints page on schedule
4. **Announcements**: Live announcements during the CTF
5. **CTF Platform**: Use built-in hint system if available

---

## Tools Participants Might Need

Consider providing a "Recommended Tools" list:
- Burp Suite Community Edition
- SQLMap
- Browser Developer Tools
- Cookie Editor extension
- curl / Postman
- hashcat / John the Ripper
- MySQL client

---

## Monitoring Progress

Track which hints are most requested to:
- Identify flags that are too difficult
- Adjust future CTF difficulty
- Provide targeted workshops
- Improve flag design
