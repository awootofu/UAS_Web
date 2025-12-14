@echo off
echo ====================================
echo Running Database Migration
echo ====================================
echo.

REM Change to project directory
cd /d C:\laragon\www\evaluationweb\web_finals

echo Running migration...
php artisan migrate

echo.
echo ====================================
echo Migration Complete!
echo ====================================
echo.
echo Next step: Update prodi data by running fresh migration OR manual SQL insert
echo.
echo Option 1 (Resets all data):
echo   php artisan migrate:fresh --seed
echo.
echo Option 2 (Keeps existing data):
echo   Run the SQL commands from CHANGES_SUMMARY.md
echo.
pause
