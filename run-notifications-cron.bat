@echo off
REM Cron Job - Processar Notificações do DoarFazBem
REM Execute a cada 5 minutos via Windows Task Scheduler

cd /d C:\laragon\www\doarfazbem
C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe spark notifications:send >> writable/logs/cron-notifications.log 2>&1
