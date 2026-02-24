@echo off
echo === Instalando Tarefa do Task Scheduler ===
echo.

schtasks /Create /TN "DoarFazBem-Notifications" /XML "%~dp0task-scheduler-config.xml" /F

if %ERRORLEVEL% EQU 0 (
    echo.
    echo [OK] Tarefa criada com sucesso!
    echo.
    echo Detalhes:
    echo   Nome: DoarFazBem-Notifications
    echo   Intervalo: A cada 5 minutos
    echo   Comando: run-notifications.bat
    echo.
    schtasks /Query /TN "DoarFazBem-Notifications" /FO LIST
) else (
    echo.
    echo [ERRO] Falha ao criar tarefa. Codigo: %ERRORLEVEL%
    echo.
)

echo.
pause
