# ========================================
# PASSO 4: VALIDACAO - DoarFazBem
# ========================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Yellow
Write-Host "PASSO 4: VALIDACAO - DoarFazBem" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Yellow
Write-Host ""

$allOk = $true

# 1. Verificar .gitignore
Write-Host "[1/6] Verificando .gitignore..." -ForegroundColor Cyan
if (Select-String -Path ".gitignore" -Pattern "firebase-credentials.json" -Quiet) {
    Write-Host "   OK - firebase-credentials.json no .gitignore" -ForegroundColor Green
} else {
    Write-Host "   ERRO - firebase-credentials.json NAO esta no .gitignore" -ForegroundColor Red
    $allOk = $false
}

if (Select-String -Path ".gitignore" -Pattern "\.env" -Quiet) {
    Write-Host "   OK - .env no .gitignore" -ForegroundColor Green
} else {
    Write-Host "   ERRO - .env NAO esta no .gitignore" -ForegroundColor Red
    $allOk = $false
}

# 2. Verificar pasta /config/
Write-Host ""
Write-Host "[2/6] Verificando pasta /config/..." -ForegroundColor Cyan
if (Test-Path "config") {
    Write-Host "   OK - Pasta config/ existe" -ForegroundColor Green
} else {
    Write-Host "   ERRO - Pasta config/ NAO existe" -ForegroundColor Red
    $allOk = $false
}

# 3. Verificar firebase-credentials.json movido
Write-Host ""
Write-Host "[3/6] Verificando firebase-credentials.json..." -ForegroundColor Cyan
if (Test-Path "config\firebase-credentials.json") {
    Write-Host "   OK - firebase-credentials.json em config/" -ForegroundColor Green
} else {
    Write-Host "   ERRO - firebase-credentials.json NAO encontrado em config/" -ForegroundColor Red
    $allOk = $false
}

if (Test-Path "firebase-credentials.json") {
    Write-Host "   AVISO - firebase-credentials.json ainda existe na raiz" -ForegroundColor Yellow
    $allOk = $false
}

# 4. Verificar backup do .env
Write-Host ""
Write-Host "[4/6] Verificando backup do .env..." -ForegroundColor Cyan
$backupFiles = Get-ChildItem -Path . -Filter ".env.backup.*" -ErrorAction SilentlyContinue
if ($backupFiles) {
    Write-Host "   OK - Backup do .env criado: $($backupFiles[0].Name)" -ForegroundColor Green
} else {
    Write-Host "   AVISO - Nenhum backup do .env encontrado" -ForegroundColor Yellow
}

# 5. Verificar se .env e firebase ainda estao no Git
Write-Host ""
Write-Host "[5/6] Verificando se arquivos sensiveis estao rastreados pelo Git..." -ForegroundColor Cyan
$gitTracked = git ls-files | Select-String -Pattern "(^\.env$|firebase-credentials\.json)"
if ($gitTracked) {
    Write-Host "   AVISO - Arquivos sensiveis ainda rastreados pelo Git:" -ForegroundColor Yellow
    $gitTracked | ForEach-Object { Write-Host "      $_" -ForegroundColor Yellow }
} else {
    Write-Host "   OK - Nenhum arquivo sensivel rastreado pelo Git" -ForegroundColor Green
}

# 6. Gerar nova Encryption Key
Write-Host ""
Write-Host "[6/6] Gerando nova Encryption Key..." -ForegroundColor Cyan
try {
    $output = php spark key:generate 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   OK - Nova encryption key gerada" -ForegroundColor Green
    } else {
        Write-Host "   ERRO - Falha ao gerar encryption key: $output" -ForegroundColor Red
        $allOk = $false
    }
} catch {
    Write-Host "   ERRO - Excecao ao gerar encryption key: $_" -ForegroundColor Red
    $allOk = $false
}

# Resultado final
Write-Host ""
Write-Host "========================================" -ForegroundColor $(if ($allOk) { "Green" } else { "Yellow" })
if ($allOk) {
    Write-Host "VALIDACAO CONCLUIDA COM SUCESSO!" -ForegroundColor Green
} else {
    Write-Host "VALIDACAO CONCLUIDA COM AVISOS" -ForegroundColor Yellow
}
Write-Host "========================================" -ForegroundColor $(if ($allOk) { "Green" } else { "Yellow" })
Write-Host ""

Write-Host "PROXIMOS PASSOS:" -ForegroundColor Cyan
Write-Host "1. Leia SEGURANCA-PASSO-3-INSTRUCOES.txt" -ForegroundColor Yellow
Write-Host "2. Rotacione TODAS as credenciais expostas:" -ForegroundColor Yellow
Write-Host "   - Asaas API Key" -ForegroundColor White
Write-Host "   - Google OAuth (Client ID + Secret)" -ForegroundColor White
Write-Host "   - Firebase Service Account" -ForegroundColor White
Write-Host "   - reCAPTCHA (Site Key + Secret)" -ForegroundColor White
Write-Host "   - Email SMTP Password" -ForegroundColor White
Write-Host "   - Google Maps API (restricoes)" -ForegroundColor White
Write-Host "3. Force push para o repositorio remoto:" -ForegroundColor Yellow
Write-Host "   git push origin master --force" -ForegroundColor White
Write-Host ""
