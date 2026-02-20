@echo off
REM Deployment Script untuk Unified POS - Windows
REM Usage: deploy-windows.bat

echo ==========================================
echo   Unified POS - Deployment Script
echo   Environment: Windows Local
echo ==========================================
echo.

set SCRIPT_DIR=%~dp0
cd /d %SCRIPT_DIR%

REM Step 1: Update Backend
echo [STEP 1] Update Backend...
cd "%SCRIPT_DIR%backend"

if not exist ".env" (
    echo [ERROR] File .env tidak ditemukan!
    echo Copy dari .env.example dan sesuaikan konfigurasi.
    pause
    exit /b 1
)

echo Installing composer dependencies...
call composer install --optimize-autoloader --no-dev
echo [OK] Composer dependencies installed

echo Running database migrations...
call php artisan migrate --force
echo [OK] Database migrations completed

echo Clearing and caching config...
call php artisan config:clear
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
echo [OK] Cache optimized

REM Step 2: Build Frontend
echo.
echo [STEP 2] Build Frontend...
cd "%SCRIPT_DIR%frontend"

if not exist ".env" (
    echo [ERROR] File .env tidak ditemukan di frontend!
    pause
    exit /b 1
)

echo Installing npm dependencies...
call npm install
echo [OK] NPM dependencies installed

echo Building frontend for production...
call npm run build
echo [OK] Frontend build completed

REM Step 3: Build Other Apps
echo.
echo [STEP 3] Build Additional Apps...

if exist "%SCRIPT_DIR%inventory-app" (
    echo Building inventory-app...
    cd "%SCRIPT_DIR%inventory-app"
    call npm install
    call npm run build
    echo [OK] Inventory app built
)

if exist "%SCRIPT_DIR%procurement-app" (
    echo Building procurement-app...
    cd "%SCRIPT_DIR%procurement-app"
    call npm install
    call npm run build
    echo [OK] Procurement app built
)

if exist "%SCRIPT_DIR%ticket-app" (
    echo Building ticket-app...
    cd "%SCRIPT_DIR%ticket-app"
    call npm install
    call npm run build
    echo [OK] Ticket app built
)

echo.
echo ==========================================
echo   Deployment completed successfully!
echo ==========================================
echo.
echo Next steps (Windows):
echo 1. Copy frontend/dist folder ke web server (IIS/Apache)
echo 2. Configure web server untuk serve frontend
echo 3. Configure web server untuk proxy /api ke backend
echo 4. Atau gunakan PHP built-in server untuk testing:
echo    cd backend ^&^& php artisan serve
echo    cd frontend ^&^& npm run preview
echo.
pause
