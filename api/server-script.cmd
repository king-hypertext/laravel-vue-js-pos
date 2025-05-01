@echo off
setlocal

REM --- Configuration ---
set "phpPath=C:\xampp\php\php.exe"      REM Adjust this to your PHP executable path
set "artisanPath=C:\2025\retail-pos\artisan" REM Adjust this to your Laravel project's artisan file path
set "serverAddress=http://127.0.0.1:8000" REM The expected server address

REM --- Function to check if the server is running ---
:checkServerStatus
curl -s --head "%serverAddress%" | findstr /I "200 OK" > nul
if %errorlevel% == 0 (
    echo PHP Artisan server is already running.
    goto :eof
) else (
    echo PHP Artisan server is not running. Starting in the background...
    goto :startServerBackground
)

REM --- Function to start the PHP Artisan server in the background ---
:startServerBackground
start /b "" php "%artisanPath%" serve
@REM echo Started PHP Artisan server in the background.
goto :eof

REM --- Main execution ---
echo Checking PHP Artisan server status...
goto :checkServerStatus

endlocal