# ========================================
# PASSO 1: LIMPEZA GIT - DoarFazBem
# ========================================
# Executando automaticamente...

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "PASSO 1: LIMPEZA GIT - DoarFazBem" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

# [1/5] Commitando mudanças no .gitignore
Write-Host "[1/5] Commitando mudancas no .gitignore..." -ForegroundColor Cyan
git add .gitignore
git commit -m "chore: Add sensitive files to .gitignore (security fix)"

Write-Host ""
Write-Host "[2/5] Removendo arquivos do Git (mantendo no disco)..." -ForegroundColor Cyan
git rm --cached .env 2>$null
git rm --cached .env.production 2>$null
git rm --cached firebase-credentials.json 2>$null

Write-Host ""
Write-Host "[3/5] Verificando se git filter-branch esta disponivel..." -ForegroundColor Cyan

# Verifica se filter-branch está disponível
$filterBranchAvailable = $true
try {
    git filter-branch --help | Out-Null
} catch {
    $filterBranchAvailable = $false
}

if ($filterBranchAvailable) {
    Write-Host ""
    Write-Host "[4/5] Removendo arquivos do historico Git..." -ForegroundColor Cyan
    Write-Host "IMPORTANTE: Isso pode demorar alguns minutos..." -ForegroundColor Yellow

    git filter-branch --force --index-filter "git rm -r --cached --ignore-unmatch .env .env.production firebase-credentials.json" --prune-empty --tag-name-filter cat -- --all

    Write-Host ""
    Write-Host "[5/5] Limpando referencias antigas..." -ForegroundColor Cyan
    git reflog expire --expire=now --all
    git gc --prune=now --aggressive
} else {
    Write-Host ""
    Write-Host "AVISO: git filter-branch nao disponivel. Usando metodo alternativo..." -ForegroundColor Yellow

    # Método alternativo: usar git filter-repo ou BFG (se disponível)
    Write-Host "Continuando com limpeza basica..." -ForegroundColor Cyan
    git reflog expire --expire=now --all
    git gc --prune=now --aggressive
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "PASSO 1 CONCLUIDO!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
