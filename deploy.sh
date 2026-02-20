#!/bin/bash

# Deployment Script untuk Unified POS
# Usage: ./deploy.sh [production|staging|local]

set -e

ENV=${1:-production}
echo "=========================================="
echo "  Unified POS - Deployment Script"
echo "  Environment: $ENV"
echo "=========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${YELLOW}➜ $1${NC}"
}

# Check if running as root
if [ "$EUID" -eq 0 ]; then 
    print_error "Jangan jalankan script ini sebagai root!"
    exit 1
fi

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Step 1: Backup Database
print_info "Step 1: Backup Database..."
if [ -f "$SCRIPT_DIR/backend/.env" ]; then
    cd "$SCRIPT_DIR/backend"
    
    # Read database credentials from .env
    DB_NAME=$(grep DB_DATABASE .env | cut -d '=' -f2)
    DB_USER=$(grep DB_USERNAME .env | cut -d '=' -f2)
    
    BACKUP_DIR="$SCRIPT_DIR/backups"
    mkdir -p "$BACKUP_DIR"
    
    BACKUP_FILE="$BACKUP_DIR/backup-$(date +%Y%m%d-%H%M%S).sql"
    
    print_info "Backing up database to $BACKUP_FILE..."
    mysqldump -u "$DB_USER" -p "$DB_NAME" > "$BACKUP_FILE" 2>/dev/null || true
    
    if [ -f "$BACKUP_FILE" ]; then
        print_success "Database backup created"
    else
        print_info "Skipping database backup (database mungkin belum ada)"
    fi
fi

# Step 2: Update Backend
print_info "Step 2: Update Backend..."
cd "$SCRIPT_DIR/backend"

if [ ! -f ".env" ]; then
    print_error "File .env tidak ditemukan! Copy dari .env.example dan sesuaikan konfigurasi."
    exit 1
fi

print_info "Installing composer dependencies..."
composer install --optimize-autoloader --no-dev --quiet
print_success "Composer dependencies installed"

print_info "Running database migrations..."
php artisan migrate --force
print_success "Database migrations completed"

print_info "Clearing and caching config..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Cache optimized"

print_info "Setting permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
print_success "Permissions set"

# Step 3: Build Frontend
print_info "Step 3: Build Frontend..."
cd "$SCRIPT_DIR/frontend"

if [ ! -f ".env" ]; then
    print_error "File .env tidak ditemukan di frontend! Copy dari .env.example."
    exit 1
fi

print_info "Installing npm dependencies..."
npm install --silent
print_success "NPM dependencies installed"

print_info "Building frontend for production..."
npm run build
print_success "Frontend build completed"

# Step 4: Build Other Apps (if needed)
if [ "$ENV" = "production" ]; then
    print_info "Step 4: Build Additional Apps..."
    
    # Build inventory-app
    if [ -d "$SCRIPT_DIR/inventory-app" ]; then
        print_info "Building inventory-app..."
        cd "$SCRIPT_DIR/inventory-app"
        npm install --silent
        npm run build
        print_success "Inventory app built"
    fi
    
    # Build procurement-app
    if [ -d "$SCRIPT_DIR/procurement-app" ]; then
        print_info "Building procurement-app..."
        cd "$SCRIPT_DIR/procurement-app"
        npm install --silent
        npm run build
        print_success "Procurement app built"
    fi
    
    # Build ticket-app
    if [ -d "$SCRIPT_DIR/ticket-app" ]; then
        print_info "Building ticket-app..."
        cd "$SCRIPT_DIR/ticket-app"
        npm install --silent
        npm run build
        print_success "Ticket app built"
    fi
fi

# Step 5: Restart Services
print_info "Step 5: Restart Services..."

# Restart PHP-FPM
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
print_info "Restarting PHP-FPM $PHP_VERSION..."
sudo systemctl restart php$PHP_VERSION-fpm
print_success "PHP-FPM restarted"

# Restart Nginx
print_info "Restarting Nginx..."
sudo nginx -t && sudo systemctl restart nginx
print_success "Nginx restarted"

# Restart Queue Worker (if exists)
if systemctl is-active --quiet kasir-queue; then
    print_info "Restarting queue worker..."
    sudo systemctl restart kasir-queue
    print_success "Queue worker restarted"
fi

# Step 6: Health Check
print_info "Step 6: Health Check..."

# Check Nginx
if systemctl is-active --quiet nginx; then
    print_success "Nginx is running"
else
    print_error "Nginx is not running!"
fi

# Check PHP-FPM
if systemctl is-active --quiet php$PHP_VERSION-fpm; then
    print_success "PHP-FPM is running"
else
    print_error "PHP-FPM is not running!"
fi

# Check MySQL
if systemctl is-active --quiet mysql; then
    print_success "MySQL is running"
else
    print_error "MySQL is not running!"
fi

echo ""
echo "=========================================="
echo -e "${GREEN}✓ Deployment completed successfully!${NC}"
echo "=========================================="
echo ""
echo "Next steps:"
echo "1. Check application at your domain"
echo "2. Monitor logs: sudo tail -f /var/log/nginx/kasir-pos-error.log"
echo "3. Monitor Laravel logs: tail -f $SCRIPT_DIR/backend/storage/logs/laravel.log"
echo ""
