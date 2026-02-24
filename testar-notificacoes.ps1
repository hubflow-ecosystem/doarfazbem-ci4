# ============================================
# Script de Teste do Sistema de Notificações
# DoarFazBem
# ============================================
#
# COMO USAR:
# 1. Abra PowerShell
# 2. Navegue até a pasta: cd C:\laragon\www\doarfazbem
# 3. Execute: .\testar-notificacoes.ps1
#
# ============================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Teste do Sistema de Notificações" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Executar comando de notificações
Write-Host "[1/1] Processando fila de notificações..." -ForegroundColor Yellow
Write-Host ""
Write-Host "---------- INÍCIO DA EXECUÇÃO ----------" -ForegroundColor Gray

php spark notifications:send

Write-Host "----------- FIM DA EXECUÇÃO -----------" -ForegroundColor Gray
Write-Host ""

if ($LASTEXITCODE -eq 0) {
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  COMANDO EXECUTADO COM SUCESSO!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
} else {
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ERRO AO EXECUTAR COMANDO!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Código de erro: $LASTEXITCODE" -ForegroundColor Red
}

Write-Host ""
Write-Host "Para verificar notificações no banco de dados:" -ForegroundColor Cyan
Write-Host "  SELECT * FROM notification_queue WHERE status = 'pending';" -ForegroundColor White
Write-Host ""
Write-Host "Para verificar logs:" -ForegroundColor Cyan
Write-Host "  writable/logs/log-" -NoNewline -ForegroundColor White
Write-Host (Get-Date -Format "yyyy-MM-dd") -NoNewline -ForegroundColor Yellow
Write-Host ".log" -ForegroundColor White
Write-Host ""

Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
