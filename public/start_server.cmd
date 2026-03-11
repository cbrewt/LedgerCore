@echo off
cd /d "%~dp0" || (
  echo ERROR: Failed to change directory.
  pause
  exit /b 1
)

start "" php -S localhost:3000
timeout /t 1 >nul
start "" chrome http://localhost:3000
