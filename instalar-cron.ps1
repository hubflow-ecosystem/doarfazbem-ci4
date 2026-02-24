# ============================================
# Script de Instalação do Cron Job
# DoarFazBem - Sistema de Notificações
# ============================================
#
# COMO USAR:
# 1. Abra PowerShell como Administrador
# 2. Navegue até a pasta: cd C:\laragon\www\doarfazbem
# 3. Execute: .\instalar-cron.ps1
#
# ============================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  DoarFazBem - Instalação Cron Job" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$taskName = "DoarFazBem-Notifications"

# Remover tarefa existente (se houver)
Write-Host "[1/2] Verificando tarefas existentes..." -ForegroundColor Yellow
$existing = schtasks /Query /TN $taskName 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "      Removendo tarefa antiga..." -ForegroundColor Gray
    schtasks /Delete /TN $taskName /F | Out-Null
}

# Criar tarefa via XML
Write-Host "[2/2] Criando tarefa em MODO SILENCIOSO..." -ForegroundColor Yellow
$result = schtasks /Create /TN $taskName /XML "C:\laragon\www\doarfazbem\task-scheduler-config.xml" /F

if ($LASTEXITCODE -eq 0) {
    Write-Host "      OK - Tarefa criada!" -ForegroundColor Green

    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  INSTALAÇÃO CONCLUÍDA COM SUCESSO!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "A tarefa irá executar:" -ForegroundColor Cyan
    Write-Host "  - A cada 5 minutos" -ForegroundColor White
    Write-Host "  - Comando: php spark notifications:send" -ForegroundColor White
    Write-Host "  - Modo: SEGUNDO PLANO (sem abrir janelas)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Para testar manualmente:" -ForegroundColor Cyan
    Write-Host "  php spark notifications:send" -ForegroundColor White
    Write-Host ""

} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ERRO AO CRIAR TAREFA!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Código de erro: $LASTEXITCODE" -ForegroundColor Red
    Write-Host ""
    Write-Host "Tente executar este script como Administrador:" -ForegroundColor Yellow
    Write-Host "  1. Clique com botão direito no PowerShell" -ForegroundColor White
    Write-Host "  2. Selecione 'Executar como Administrador'" -ForegroundColor White
    Write-Host ""
}

Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
