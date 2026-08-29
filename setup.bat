@echo off
setlocal
echo NextGen LMS Setup
echo =================
echo.
echo Make sure XAMPP MySQL service is running before proceeding.
echo.
pause
echo.
set "PROJECT_ROOT=%~dp0"
set "PHP_EXE=C:\xampp\php\php.exe"

if not exist "%PHP_EXE%" (
    echo PHP was not found at %PHP_EXE%
    echo Update setup.bat or run the setup manually with your local PHP executable.
    echo Example: php setup\setup.php
    echo.
    pause
    exit /b 1
)

"%PHP_EXE%" "%PROJECT_ROOT%setup\setup.php"
echo.
echo Setup complete! Press any key to exit.
pause >nul
