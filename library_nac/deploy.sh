#!/bin/bash

# Library CTF - Quick Deployment Script for Ubuntu Server
# This script will install Docker, Docker Compose, and deploy the application

set -e

echo "======================================"
echo "Library CTF Deployment Script"
echo "======================================"
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root (use sudo)" 
   exit 1
fi

# Update system
echo "Updating system packages..."

# Install dependencies
echo "Installing dependencies..."
apt-get install -y \
    apt-transport-https \
    ca-certificates \
    curl \
    gnupg \
    lsb-release \
    git

# Install Docker
echo "Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    
    # Start and enable Docker
    systemctl start docker
    systemctl enable docker
    
    echo "Docker installed successfully!"
else
    echo "Docker is already installed"
fi

# Install Docker Compose
echo "Installing Docker Compose..."
if ! command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep 'tag_name' | cut -d\" -f4)
    curl -L "https://github.com/docker/compose/releases/download/${DOCKER_COMPOSE_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    
    echo "Docker Compose installed successfully!"
else
    echo "Docker Compose is already installed"
fi

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')

echo ""
echo "======================================"
echo "Starting application deployment..."
echo "======================================"
echo ""

# Build and start containers
echo "Building Docker containers..."
docker-compose build

echo "Starting Docker containers..."
docker-compose up -d

# Wait for services to be ready
echo "Waiting for services to start..."
sleep 10

# Check if services are running
if docker-compose ps | grep -q "Up"; then
    echo ""
    echo "======================================"
    echo "Deployment Successful!"
    echo "======================================"
    echo ""
    echo "Application is now running at:"
    echo "  - Local: http://localhost:8080"
    echo "  - Network: http://${SERVER_IP}:8080"
    echo ""
    echo "Database is running on port 3306"
    echo "  - Host: ${SERVER_IP}:3306"
    echo "  - Database: library_ctf"
    echo "  - User: library_user"
    echo "  - Password: library_pass_2024"
    echo ""
    echo "Test accounts:"
    echo "  - User: john_doe / password"
    echo "  - Librarian: librarian_admin / library2024"
    echo ""
    echo "To view logs: docker-compose logs -f"
    echo "To stop: docker-compose down"
    echo "To restart: docker-compose restart"
    echo ""
else
    echo "Error: Services failed to start properly"
    echo "Check logs with: docker-compose logs"
    exit 1
fi

# Configure firewall (if ufw is installed)
if command -v ufw &> /dev/null; then
    echo "Configuring firewall..."
    ufw allow 8080/tcp
    ufw allow 22/tcp
    echo "Firewall configured"
fi

echo "======================================"
echo "Setup Complete!"
echo "======================================"
