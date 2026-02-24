@echo off
echo ======================================
echo  INSTALANDO CRON DE RIFAS
echo ======================================
echo.

:: Criar tarefa agendada
schtasks /Create /TN "DoarFazBem-Raffles-Cleanup" /TR "c:\laragon\bin\php\php-8.2.24-nts-Win32-vs16-x64\php.exe c:\laragon\www\doarfazbem\spark raffles:clean" /SC MINUTE /MO 5 /F

echo.
echo Tarefa instalada! Executando teste...
echo.

:: Executar uma vez
c:\laragon\bin\php\php-8.2.24-nts-Win32-vs16-x64\php.exe c:\laragon\www\doarfazbem\spark raffles:clean

echo.
echo ======================================
echo  INSTALACAO CONCLUIDA!
echo ======================================
pause
