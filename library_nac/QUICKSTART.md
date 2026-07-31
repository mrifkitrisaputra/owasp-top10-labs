# Library CTF - Quick Start Guide

## Quick Deployment (5 minutes)

### Option 1: Automated Deployment (Recommended)

```bash
# 1. Upload project to server
scp -r web4/ user@your-server-ip:/opt/

# 2. SSH to server
ssh user@your-server-ip

# 3. Navigate to project
cd /opt/web4

# 4. Run deployment
chmod +x deploy.sh
sudo ./deploy.sh
```

### Option 2: Manual Deployment

```bash
# 1. Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# 2. Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# 3. Start application
cd /opt/web4
sudo docker-compose up -d

# 4. Check status
sudo docker-compose ps
```

## Access Points

After deployment:

- **Website**: `http://YOUR_IP:8080`
- **Database**: `YOUR_IP:3306`

## Test Credentials

```
Regular User:
  Username: john_doe
  Password: password

Librarian (Admin):
  Username: librarian_admin
  Password: library2024
```

## Useful Commands

```bash
# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Stop services
docker-compose down

# Reset everything
docker-compose down -v
docker-compose up -d

# Access MySQL
docker exec -it library_mysql mysql -u root -p
# Password: root_password_2024
```

## Firewall Configuration

If using UFW:

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 8080/tcp  # Web application
sudo ufw enable
```

## Troubleshooting

### Services won't start
```bash
# Check logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Database connection error
```bash
# Wait for MySQL to initialize (first run takes ~30 seconds)
docker-compose logs mysql

# Or restart database
docker-compose restart mysql
```

### Port already in use
```bash
# Check what's using port 8080
sudo lsof -i :8080

# Change port in docker-compose.yml
# ports:
#   - "8081:80"  # Change 8080 to 8081
```

## CTF Setup Checklist

- [ ] Deploy to isolated server
- [ ] Test all 11 flags work correctly
- [ ] Configure firewall rules
- [ ] Set up monitoring (optional)
- [ ] Prepare hint schedule
- [ ] Brief participants on rules
- [ ] Provide server IP and access info
- [ ] Start CTF timer

## Security Reminders

⚠️ **This is a vulnerable application by design!**

- Only use in isolated CTF environments
- Do not expose to public internet without protection
- Monitor all activities
- Reset after CTF completion

## Flag Overview

| Flag | Difficulty | Concept | Estimated Time |
|------|-----------|---------|----------------|
| 1 | ⭐ Easy | SQL Injection | Week 1-2 |
| 2 | ⭐ Easy | Info Disclosure | Week 1-2 |
| 3 | ⭐⭐ Medium | Cookie Manipulation | Week 2-3 |
| 4 | ⭐⭐ Medium | Session Analysis | Week 3-4 |
| 5 | ⭐⭐⭐ Medium-Hard | File Inclusion | Week 4-5 |
| 6 | ⭐⭐⭐ Hard | Complex SQL | Week 5-6 |
| 7 | ⭐⭐⭐⭐ Hard | Auth Bypass | Week 6-7 |
| 8 | ⭐⭐⭐⭐ Hard | Weak Crypto | Week 7-8 |
| 9 | ⭐⭐⭐⭐⭐ Very Hard | IDOR | Week 8-9 |
| 10 | ⭐⭐⭐⭐⭐ Very Hard | Privilege Escalation | Week 9-10 |
| 11 | ⭐⭐⭐⭐⭐ Very Hard | Complex Chain | Week 10-12 |

## Support

For detailed flag solutions, see [README.md](README.md)

For technical issues during deployment, check:
- Docker logs: `docker-compose logs`
- Container status: `docker-compose ps`
- System resources: `htop` or `docker stats`

---

**Ready to start the CTF? Good luck! 🚀**
