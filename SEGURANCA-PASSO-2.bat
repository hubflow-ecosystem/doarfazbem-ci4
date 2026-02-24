@echo off
echo ========================================
echo PASSO 2: MOVER CREDENCIAIS - DoarFazBem
echo ========================================
echo.
echo Este script vai:
echo 1. Criar pasta /config/ para credenciais
echo 2. Mover firebase-credentials.json para /config/
echo 3. Criar backup do .env atual
echo.
pause

echo.
echo [1/3] Criando pasta /config/...
if not exist "config" mkdir config

echo.
echo [2/3] Movendo firebase-credentials.json...
if exist "firebase-credentials.json" (
    move firebase-credentials.json config\firebase-credentials.json
    echo Firebase credentials movido para config\
) else (
    echo Arquivo firebase-credentials.json nao encontrado
)

echo.
echo [3/3] Criando backup do .env...
if exist ".env" (
    copy .env .env.backup.%date:~-4,4%%date:~-7,2%%date:~-10,2%
    echo Backup criado: .env.backup.%date:~-4,4%%date:~-7,2%%date:~-10,2%
) else (
    echo Arquivo .env nao encontrado
)

echo.
echo ========================================
echo CONCLUIDO!
echo ========================================
echo.
echo Credenciais movidas para a pasta config\
echo Esta pasta NAO deve ser commitada no Git
echo.
echo Proximo passo:
echo Execute SEGURANCA-PASSO-3-INSTRUCOES.txt
echo.
pause
