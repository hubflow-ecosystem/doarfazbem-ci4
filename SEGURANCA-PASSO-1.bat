@echo off
echo ========================================
echo PASSO 1: LIMPEZA GIT - DoarFazBem
echo ========================================
echo.
echo Este script vai:
echo 1. Remover .env do historico Git
echo 2. Remover firebase-credentials.json do historico Git
echo 3. Adicionar arquivos ao .gitignore
echo.
echo ATENCAO: Isso vai reescrever o historico do Git!
echo.
pause

echo.
echo [1/5] Adicionando arquivos ao .gitignore...
echo .env >> .gitignore
echo .env.production >> .gitignore
echo firebase-credentials.json >> .gitignore
echo /config/firebase-credentials.json >> .gitignore

echo.
echo [2/5] Removendo arquivos do Git (mantendo no disco)...
git rm --cached .env 2>nul
git rm --cached .env.production 2>nul
git rm --cached firebase-credentials.json 2>nul

echo.
echo [3/5] Commitando mudancas no .gitignore...
git add .gitignore
git commit -m "chore: Add sensitive files to .gitignore"

echo.
echo [4/5] Removendo arquivos do historico Git...
echo IMPORTANTE: Isso pode demorar alguns minutos...
git filter-branch --force --index-filter "git rm -r --cached --ignore-unmatch .env .env.production firebase-credentials.json" --prune-empty --tag-name-filter cat -- --all

echo.
echo [5/5] Limpando referencias antigas...
git reflog expire --expire=now --all
git gc --prune=now --aggressive

echo.
echo ========================================
echo CONCLUIDO!
echo ========================================
echo.
echo Proximos passos:
echo 1. Execute SEGURANCA-PASSO-2.bat para mover credenciais
echo 2. Execute SEGURANCA-PASSO-3.bat para rotacionar chaves
echo.
pause
