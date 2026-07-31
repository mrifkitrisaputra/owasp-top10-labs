#!/bin/bash

# Stop and remove all containers
echo "Stopping containers..."
docker-compose down

# Remove volumes (optional - will delete database data)
read -p "Do you want to remove database data? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Removing volumes..."
    docker-compose down -v
fi

# Remove Docker images
read -p "Do you want to remove Docker images? (y/N) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Removing images..."
    docker rmi library_webapp library_mysql 2>/dev/null || true
fi

echo "Cleanup complete!"
