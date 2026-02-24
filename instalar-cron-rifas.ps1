# ===================================================
# INSTALADOR CRON - LIMPEZA DE RESERVAS EXPIRADAS
# ===================================================
# Este script instala uma tarefa agendada no Windows
# para limpar reservas de rifas expiradas a cada 5 minutos
# ===================================================

$ErrorActionPreference = "Stop"

# Configuracoes
$taskName = "DoarFazBem-Raffles-Cleanup"
$projectPath = "c:\laragon\www\doarfazbem"
$phpPath = "c:\laragon\bin\php\php-8.2.24-nts-Win32-vs16-x64\php.exe"
$sparkCommand = "$phpPath $projectPath\spark raffles:clean"
$logFile = "$projectPath\writable\logs\raffles-cleanup.log"

Write-Host "======================================" -ForegroundColor Cyan
Write-Host " INSTALANDO CRON DE RIFAS" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se tarefa ja existe
$existingTask = Get-ScheduledTask -TaskName $taskName -ErrorAction SilentlyContinue
if ($existingTask) {
    Write-Host "Removendo tarefa existente..." -ForegroundColor Yellow
    Unregister-ScheduledTask -TaskName $taskName -Confirm:$false
}

# Criar acao
$action = New-ScheduledTaskAction `
    -Execute "cmd.exe" `
    -Argument "/c `"$sparkCommand >> $logFile 2>&1`"" `
    -WorkingDirectory $projectPath

# Criar gatilho (a cada 5 minutos)
$trigger = New-ScheduledTaskTrigger `
    -Once `
    -At (Get-Date) `
    -RepetitionInterval (New-TimeSpan -Minutes 5) `
    -RepetitionDuration ([TimeSpan]::MaxValue)

# Configuracoes da tarefa
$settings = New-ScheduledTaskSettingsSet `
    -AllowStartIfOnBatteries `
    -DontStopIfGoingOnBatteries `
    -StartWhenAvailable `
    -ExecutionTimeLimit (New-TimeSpan -Minutes 10) `
    -MultipleInstances IgnoreNew

# Registrar tarefa
Register-ScheduledTask `
    -TaskName $taskName `
    -Action $action `
    -Trigger $trigger `
    -Settings $settings `
    -Description "DoarFazBem - Limpa reservas de rifas expiradas a cada 5 minutos" `
    -RunLevel Highest `
    -Force | Out-Null

Write-Host ""
Write-Host "Tarefa '$taskName' instalada com sucesso!" -ForegroundColor Green
Write-Host ""
Write-Host "Configuracoes:" -ForegroundColor Yellow
Write-Host "  Comando: $sparkCommand" -ForegroundColor Gray
Write-Host "  Intervalo: A cada 5 minutos" -ForegroundColor Gray
Write-Host "  Log: $logFile" -ForegroundColor Gray
Write-Host ""

# Executar uma vez para testar
Write-Host "Executando teste..." -ForegroundColor Yellow
Start-ScheduledTask -TaskName $taskName
Start-Sleep -Seconds 3

# Verificar resultado
$taskInfo = Get-ScheduledTaskInfo -TaskName $taskName
Write-Host ""
Write-Host "Status da tarefa:" -ForegroundColor Cyan
Write-Host "  Ultima execucao: $($taskInfo.LastRunTime)" -ForegroundColor Gray
Write-Host "  Resultado: $($taskInfo.LastTaskResult)" -ForegroundColor Gray
Write-Host "  Proxima execucao: $($taskInfo.NextRunTime)" -ForegroundColor Gray
Write-Host ""
Write-Host "======================================" -ForegroundColor Green
Write-Host " INSTALACAO CONCLUIDA!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Green
