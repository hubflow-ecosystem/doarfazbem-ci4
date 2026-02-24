# ============================================
# SOLUÇÃO FINAL DEFINITIVA - ZERO JANELAS
# Usa PowerShell com -WindowStyle Hidden
# ============================================

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  INSTALAÇÃO FINAL - ZERO JANELAS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$taskName = "DoarFazBem-Notifications"

# Remover tarefa antiga
Write-Host "[1/2] Removendo tarefa antiga..." -ForegroundColor Yellow
Unregister-ScheduledTask -TaskName $taskName -Confirm:$false -ErrorAction SilentlyContinue

# Criar ação que executa PowerShell OCULTO
Write-Host "[2/2] Criando tarefa INVISÍVEL..." -ForegroundColor Yellow

$action = New-ScheduledTaskAction `
    -Execute "powershell.exe" `
    -Argument "-WindowStyle Hidden -NoLogo -NonInteractive -NoProfile -ExecutionPolicy Bypass -File `"C:\laragon\www\doarfazbem\run-background.ps1`""

$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date) `
    -RepetitionInterval (New-TimeSpan -Minutes 5) `
    -RepetitionDuration (New-TimeSpan -Days 999)

$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -Hidden `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 2)

# IMPORTANTE: Executar sem mostrar console
$principal = New-ScheduledTaskPrincipal `
    -UserId "SYSTEM" `
    -LogonType ServiceAccount `
    -RunLevel Highest

try {
    Register-ScheduledTask `
        -TaskName $taskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Force | Out-Null

    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "  SUCESSO! ZERO JANELAS GARANTIDO" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Configuração:" -ForegroundColor Cyan
    Write-Host "  - Usuário: SYSTEM (sem interface)" -ForegroundColor White
    Write-Host "  - PowerShell: -WindowStyle Hidden" -ForegroundColor White
    Write-Host "  - Modo: Totalmente invisível" -ForegroundColor Green
    Write-Host ""

} catch {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "  ERRO AO CRIAR COMO SYSTEM!" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Tentando com usuário atual..." -ForegroundColor Yellow

    $principal = New-ScheduledTaskPrincipal `
        -UserId "$env:USERNAME" `
        -LogonType Interactive `
        -RunLevel Limited

    Register-ScheduledTask `
        -TaskName $taskName `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Force | Out-Null

    Write-Host "OK - Criado com usuário atual" -ForegroundColor Green
}

Write-Host ""
Write-Host "Status atual:" -ForegroundColor Cyan
Get-ScheduledTask -TaskName $taskName | Select-Object TaskName, State | Format-List

Write-Host ""
Write-Host "Pressione qualquer tecla para fechar..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
