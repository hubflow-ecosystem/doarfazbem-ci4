@echo off
REM Script para processar fila de notificações do DoarFazBem
REM Este arquivo é executado pelo Task Scheduler a cada 5 minutos

cd /d C:\laragon\www\doarfazbem
C:\laragon\bin\php\php-8.3.26-Win32-vs16-x64\php.exe spark notifications:send

REM Log opcional (descomente se quiser)
REM >> C:\laragon\www\doarfazbem\writable\logs\cron.log 2>&1
