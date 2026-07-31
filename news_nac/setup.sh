#!/bin/bash
#
# Nac News Portal - Quick Setup Script
# Untuk Ubuntu Server dengan Docker terinstall
#

set -e

echo "============================================"
echo "  Nac News Portal - CTF Setup"
echo "============================================"
echo ""

# Check Docker
if ! command -v docker &> /dev/null; then
    echo "[!] Docker tidak ditemukan. Menginstall Docker..."
    curl -fsSL https://get.docker.com | sh
    sudo usermod -aG docker $USER
    echo "[+] Docker terinstall. Silakan logout dan login kembali jika perlu."
fi

# Check Docker Compose
if ! docker compose version &> /dev/null; then
    echo "[!] Docker Compose tidak ditemukan."
    echo "    Install: sudo apt install docker-compose-plugin"
    exit 1
fi

echo "[*] Docker detected: $(docker --version)"
echo "[*] Compose detected: $(docker compose version)"
echo ""

# Check port 80
if ss -tlnp | grep -q ':80 ' 2>/dev/null; then
    echo "[!] WARNING: Port 80 sudah digunakan."
    echo "    Hentikan service yang menggunakan port 80 atau ubah port di docker-compose.yml"
    read -p "    Lanjutkan? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Build and start
echo "[*] Building containers..."
docker compose build --no-cache

echo "[*] Starting services..."
docker compose up -d

echo ""
echo "[*] Menunggu MySQL ready..."
sleep 5

# Wait for healthy
RETRIES=30
until docker compose exec -T db mysqladmin ping -h localhost -u root --password=nac_root_2024 --silent 2>/dev/null; do
    RETRIES=$((RETRIES - 1))
    if [ $RETRIES -le 0 ]; then
        echo "[!] MySQL tidak bisa start dalam waktu yang diharapkan."
        echo "    Cek log: docker compose logs db"
        exit 1
    fi
    echo "    Waiting for MySQL... ($RETRIES attempts remaining)"
    sleep 2
done

echo "[+] MySQL ready!"
echo ""

# Check all containers running
echo "[*] Container status:"
docker compose ps
echo ""

# Get IP
SERVER_IP=$(hostname -I | awk '{print $1}' 2>/dev/null || echo "localhost")

echo "============================================"
echo "  Setup Complete!"
echo "============================================"
echo ""
echo "  URL:      http://${SERVER_IP}"
echo "  Login:    reader / reader2024"
echo ""
echo "  Management:"
echo "    Logs:    docker compose logs -f"
echo "    Stop:    docker compose down"
echo "    Reset:   docker compose down -v && docker compose up -d --build"
echo ""
echo "============================================"
