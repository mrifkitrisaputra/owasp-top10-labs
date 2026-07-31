#!/bin/bash
# =============================================
# NAC Cafe CTF - Quick Setup Script
# For Ubuntu Server with Public IP
# =============================================

set -e

echo "============================================="
echo "  NAC Cafe CTF - Quick Setup"
echo "============================================="
echo ""

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo "[!] Please run this script with sudo:"
    echo "    sudo ./setup.sh"
    exit 1
fi

# 1. Install Docker if not present
if ! command -v docker &> /dev/null; then
    echo "[*] Installing Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl enable docker
    systemctl start docker
    echo "[+] Docker installed successfully"
else
    echo "[+] Docker already installed"
fi

# 2. Install Docker Compose if not present
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo "[*] Installing Docker Compose..."
    COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep '"tag_name"' | sed -E 's/.*"([^"]+)".*/\1/')
    curl -L "https://github.com/docker/compose/releases/download/${COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    echo "[+] Docker Compose installed successfully"
else
    echo "[+] Docker Compose already installed"
fi

# 3. Open port 80 if ufw is active
if command -v ufw &> /dev/null; then
    if ufw status | grep -q "Status: active"; then
        echo "[*] Opening port 80 in firewall..."
        ufw allow 80/tcp
        echo "[+] Port 80 opened"
    fi
fi

# 4. Build and start containers
echo ""
echo "[*] Building and starting NAC Cafe CTF..."
echo "    This may take a few minutes on first run..."
echo ""

# Use docker compose (v2) or docker-compose (v1)
if docker compose version &> /dev/null 2>&1; then
    docker compose up -d --build
else
    docker-compose up -d --build
fi

echo ""
echo "[*] Waiting for services to initialize..."
sleep 15

# 5. Check if services are running
echo ""
echo "============================================="
echo "  NAC Cafe CTF - Setup Complete!"
echo "============================================="
echo ""

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')

if docker compose version &> /dev/null 2>&1; then
    docker compose ps
else
    docker-compose ps
fi

echo ""
echo "  Access URL  : http://${SERVER_IP}/"
echo "  Credentials : guest / guest123"
echo "  Total Flags : 11"
echo ""
echo "  Manage:"
echo "    Stop  : docker-compose down"
echo "    Reset : docker-compose down -v && docker-compose up -d --build"
echo "    Logs  : docker-compose logs -f web"
echo ""
echo "============================================="
