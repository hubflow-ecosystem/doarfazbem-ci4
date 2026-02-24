@echo off
echo ========================================
echo    CANTINA.AI - Ngrok Tunnel
echo ========================================
echo.

REM Matar processos ngrok antigos
taskkill /F /IM ngrok.exe 2>nul
timeout /t 2 /nobreak >nul

REM Iniciar ngrok
start "Ngrok Cantina" ngrok start cantina

REM Aguardar iniciar
timeout /t 5 /nobreak >nul

echo.
echo Ngrok iniciado para Cantina.AI
echo Dashboard: http://localhost:4040
echo.
pause
