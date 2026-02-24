# ========================================
# PASSO 2: MOVER CREDENCIAIS - DoarFazBem
# ========================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "PASSO 2: MOVER CREDENCIAIS - DoarFazBem" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

Write-Host "[1/3] Criando pasta /config/..." -ForegroundColor Cyan
if (!(Test-Path "config")) {
    New-Item -ItemType Directory -Path "config" | Out-Null
    Write-Host "Pasta config/ criada" -ForegroundColor Green
} else {
    Write-Host "Pasta config/ ja existe" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[2/3] Movendo firebase-credentials.json..." -ForegroundColor Cyan
if (Test-Path "firebase-credentials.json") {
    Move-Item -Path "firebase-credentials.json" -Destination "config\firebase-credentials.json" -Force
    Write-Host "Firebase credentials movido para config\" -ForegroundColor Green
} else {
    Write-Host "Arquivo firebase-credentials.json nao encontrado na raiz" -ForegroundColor Yellow

    if (Test-Path "config\firebase-credentials.json") {
        Write-Host "Arquivo ja existe em config\" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "[3/3] Criando backup do .env..." -ForegroundColor Cyan
if (Test-Path ".env") {
    $date = Get-Date -Format "yyyyMMdd"
    Copy-Item -Path ".env" -Destination ".env.backup.$date" -Force
    Write-Host "Backup criado: .env.backup.$date" -ForegroundColor Green
} else {
    Write-Host "Arquivo .env nao encontrado" -ForegroundColor Red
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "PASSO 2 CONCLUIDO!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Credenciais movidas para a pasta config\" -ForegroundColor Cyan
Write-Host "Esta pasta NAO deve ser commitada no Git" -ForegroundColor Yellow
Write-Host ""
