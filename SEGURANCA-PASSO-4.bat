@echo off
echo ========================================
echo PASSO 4: VALIDAR SEGURANCA - DoarFazBem
echo ========================================
echo.
echo Este script vai validar se tudo foi configurado corretamente
echo.
pause

echo.
echo Verificando arquivos...
echo.

set ERRORS=0

if exist ".env" (
    echo [OK] .env encontrado
) else (
    echo [ERRO] .env nao encontrado!
    set /a ERRORS+=1
)

if exist "config\firebase-credentials.json" (
    echo [OK] Firebase credentials em config\
) else (
    echo [AVISO] Firebase credentials nao encontrado em config\
)

if exist ".gitignore" (
    findstr /C:".env" .gitignore >nul
    if %ERRORLEVEL% EQU 0 (
        echo [OK] .env no .gitignore
    ) else (
        echo [ERRO] .env NAO esta no .gitignore!
        set /a ERRORS+=1
    )
) else (
    echo [ERRO] .gitignore nao encontrado!
    set /a ERRORS+=1
)

echo.
echo ========================================
if %ERRORS% EQU 0 (
    echo VALIDACAO CONCLUIDA COM SUCESSO!
    echo.
    echo Sua aplicacao esta mais segura agora.
    echo.
    echo Proximo passo:
    echo Execute FASE-2-INFRAESTRUTURA.txt
) else (
    echo ENCONTRADOS %ERRORS% ERROS!
    echo.
    echo Revise os passos anteriores antes de continuar.
)
echo ========================================
echo.
pause
